<?php
// QR Code Generator API
// Logs each QR generation and returns a proxy URL to the generated PNG

date_default_timezone_set('Asia/Manila');
error_reporting(E_ALL);
ini_set('display_errors', 0);
header('Content-Type: application/json');

// Include config and DB connection
if (!file_exists(__DIR__ . '/../config.php')) {
    echo json_encode(['success' => false, 'message' => 'Config file not found.']);
    exit;
}
require_once __DIR__ . '/../config.php';
$pdo = function_exists('getDbConnection') ? getDbConnection() : null;
if (!$pdo || !($pdo instanceof PDO)) {
    try {
        $dsn = "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4";
        $pdo = new PDO($dsn, $db_user, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => 'DB connection failed.']);
        exit;
    }
}

// Validate input
if (!isset($_GET['student_id']) || empty(trim($_GET['student_id']))) {
    echo json_encode(['success' => false, 'message' => 'student_id missing']);
    exit;
}
$studentId = trim($_GET['student_id']);

// Determine size
$size = isset($_GET['size']) ? preg_replace('/[^0-9x]/', '', $_GET['size']) : '64x64';
if (!preg_match('/^\d+x\d+$/', $size)) {
    $size = '64x64';
}

// Ensure logging table exists
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS `qr_generator_logs` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `student_id` VARCHAR(100) NOT NULL,
        `qr_data` TEXT NOT NULL,
        `generated_at` DATETIME NOT NULL,
        INDEX (`student_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
} catch (Exception $e) {
    error_log('QR log table create error: ' . $e->getMessage());
}

// Assemble optional parameters per goQR API
// Docs: https://goqr.me/api/doc/create-qr-code/
$ecc = isset($_GET['ecc']) && preg_match('/^[LMQH]$/', strtoupper($_GET['ecc'])) ? strtoupper($_GET['ecc']) : 'L';
$qzone = isset($_GET['qzone']) && is_numeric($_GET['qzone']) ? max(0, min(100, (int)$_GET['qzone'])) : 4; // sensible default
$margin = isset($_GET['margin']) && is_numeric($_GET['margin']) ? max(0, min(50, (int)$_GET['margin'])) : 1;
$format = 'png'; // force PNG for caching

// Color sanitization (hex short/long or rgb-r-g-b). We will forward valid values only.
function sanitizeColor($value) {
    $value = trim($value);
    if (preg_match('/^[0-9]{1,3}-[0-9]{1,3}-[0-9]{1,3}$/', $value)) {
        [$r, $g, $b] = array_map('intval', explode('-', $value));
        if ($r <= 255 && $g <= 255 && $b <= 255) return "$r-$g-$b";
        return null;
    }
    if (preg_match('/^[a-fA-F0-9]{3}$/', $value) || preg_match('/^[a-fA-F0-9]{6}$/', $value)) {
        return strtolower($value);
    }
    return null;
}

$color = isset($_GET['color']) ? sanitizeColor($_GET['color']) : null;
$bgcolor = isset($_GET['bgcolor']) ? sanitizeColor($_GET['bgcolor']) : null;

// Build QR data and remote API URL
$qrData = $studentId;
$query = [
    'data' => $qrData,
    'size' => $size,
    'ecc' => $ecc,
    'qzone' => $qzone,
    'margin' => $margin,
    'format' => $format,
];
if ($color) $query['color'] = $color;
if ($bgcolor) $query['bgcolor'] = $bgcolor;

$apiBase = 'https://api.qrserver.com/v1/create-qr-code/';
$apiUrl = $apiBase . '?' . http_build_query($query);

// Prepare local cache path
$assetsDir = realpath(__DIR__ . '/../../assets');
if ($assetsDir === false) {
    $assetsDir = __DIR__ . '/../../assets';
}
$qrDir = $assetsDir . '/qrcodes';
if (!is_dir($qrDir)) {
    @mkdir($qrDir, 0755, true);
}

// Safe filename from student id + parameters to avoid collisions
$safeId = preg_replace('/[^A-Za-z0-9_\-]/', '_', $studentId);
$fileName = $safeId . "__{$size}__ecc-{$ecc}__qz-{$qzone}.png";
$filePath = $qrDir . '/' . $fileName;

// If file exists, reuse cache
if (file_exists($filePath) && filesize($filePath) > 0) {
    try {
        $stmt = $pdo->prepare("INSERT INTO qr_generator_logs (student_id, qr_data, generated_at) VALUES (?, ?, NOW())");
        $stmt->execute([$studentId, $qrData]);
    } catch (Exception $e) {
        error_log('QR log insert error (cache hit): ' . $e->getMessage());
    }
    echo json_encode([
        'success' => true,
        'cached' => true,
        'qr_path' => 'assets/qrcodes/' . $fileName,
        'qr_file' => $fileName,
    ]);
    exit;
}

// Download from API and save locally using cURL
$ch = curl_init($apiUrl);
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => 15,
    CURLOPT_CONNECTTIMEOUT => 10,
    CURLOPT_SSL_VERIFYHOST => 2,
    CURLOPT_SSL_VERIFYPEER => true,
    CURLOPT_USERAGENT => 'CCSDays QR Cacher/1.0',
]);
$imageData = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlErr = curl_error($ch);
curl_close($ch);

if ($imageData === false || $httpCode !== 200) {
    error_log('QR download failed: HTTP ' . $httpCode . ' Err: ' . $curlErr);
    echo json_encode(['success' => false, 'message' => 'Failed to generate QR image.']);
    exit;
}

// Save file
$saved = @file_put_contents($filePath, $imageData);
if ($saved === false) {
    error_log('QR save failed: ' . $filePath);
    echo json_encode(['success' => false, 'message' => 'Failed to save QR image.']);
    exit;
}

// Log generation
try {
    $stmt = $pdo->prepare("INSERT INTO qr_generator_logs (student_id, qr_data, generated_at) VALUES (?, ?, NOW())");
    $stmt->execute([$studentId, $qrData]);
} catch (Exception $e) {
    error_log('QR log insert error: ' . $e->getMessage());
}

// Return cached path relative to project root
echo json_encode([
    'success' => true,
    'cached' => false,
    'qr_path' => 'assets/qrcodes/' . $fileName,
    'qr_file' => $fileName,
]);
