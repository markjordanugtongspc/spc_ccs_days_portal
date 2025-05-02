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

// Build QR URL
$qrData = $studentId;
$apiUrl = 'https://api.qrserver.com/v1/create-qr-code/?data=' . urlencode($qrData) . '&size=' . $size;

// Log generation
try {
    $stmt = $pdo->prepare("INSERT INTO qr_generator_logs (student_id, qr_data, generated_at) VALUES (?, ?, NOW())");
    $stmt->execute([$studentId, $qrData]);
} catch (Exception $e) {
    error_log('QR log insert error: ' . $e->getMessage());
}

// Return API URL
echo json_encode(['success' => true, 'qr_url' => $apiUrl]);
