<?php
date_default_timezone_set('Asia/Manila');
// --- Error Handling Setup ---
// Absolutely prevent any HTML error output that would break JSON
error_reporting(E_ALL); // Report all errors for logging
ini_set('display_errors', 0); // Do NOT display errors
ini_set('display_startup_errors', 0); // Do NOT display startup errors

// Start output buffering to catch any accidental output
ob_start();

// Set header FIRST, before any potential accidental output
header('Content-Type: application/json');

// --- Session & DB Connection ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Wrap require_once in try-catch to handle connection script errors gracefully
try {
    $configPath = __DIR__ . '/../config.php';
    if (!file_exists($configPath)) {
        throw new Exception("Config file not found at: $configPath");
    }
    require_once $configPath;
    // Check if getDbConnection function exists
    if (function_exists('getDbConnection')) {
        $pdo = getDbConnection(); // Function from your config.php
        // Test if PDO object is valid
        if (!isset($pdo) || !$pdo instanceof PDO) {
            throw new Exception("getDbConnection() did not return a valid PDO object");
        }
    } else {
        // If function doesn't exist, try to create a connection directly using common config variables
        if (!isset($db_host) || !isset($db_name) || !isset($db_user) || !isset($db_pass)) {
            throw new Exception("getDbConnection() not defined and database configuration variables are missing");
        }
        $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $pdo = new PDO($dsn, $db_user, $db_pass, $options);
    }
} catch (Throwable $e) { // Catch any error/exception during include/connection
    error_log("Failed to include DB connection or connect: " . $e->getMessage() . " | File: " . $e->getFile() . " | Line: " . $e->getLine());
    // Clear any buffered output
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit; // Crucial: stop script execution
}

// --- Input Validation ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit; // Crucial
}

if (!isset($_POST['qr_code']) || empty(trim($_POST['qr_code']))) {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'QR code data is missing.']);
    exit; // Crucial
}

$scannedQrCode = trim($_POST['qr_code']);

// --- Logic Implementation ---
try {
    $studentId = $scannedQrCode;
    // Validate student exists
    $stmtStudent = $pdo->prepare("SELECT Student_ID FROM students WHERE Student_ID = ?");
    $stmtStudent->execute([$studentId]);
    if (! $stmtStudent->fetchColumn()) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => "Student ID '$studentId' not found."]);
        exit;
    }
    // Determine today's date and current time in Manila
    $manilaNow = new DateTime('now', new DateTimeZone('Asia/Manila'));
    $today = $manilaNow->format('Y-m-d');
    $currentTime = $manilaNow->format('Y-m-d H:i:s');

    // Find open attendance record (signed in but not out) for today
    $stmtLatest = $pdo->prepare(
        "SELECT Attendance_ID FROM attendance
         WHERE Student_ID = ? AND DATE(Sign_In_Time) = ? AND (Sign_Out_Time IS NULL OR Sign_Out_Time = '')
         ORDER BY Sign_In_Time DESC LIMIT 1"
    );
    $stmtLatest->execute([$studentId, $today]);
    $latest = $stmtLatest->fetch(PDO::FETCH_ASSOC);
    if ($latest) {
        // Sign out existing entry
        $stmtUpdate = $pdo->prepare("UPDATE attendance SET Sign_Out_Time = ?, QR_Code = ?, Sign_In_Time = Sign_In_Time WHERE Attendance_ID = ?");
        $stmtUpdate->execute([$currentTime, $scannedQrCode, $latest['Attendance_ID']]);
        $message = "Student $studentId signed out successfully.";
    } else {
        // Sign in new entry
        $stmtInsert = $pdo->prepare("INSERT INTO attendance (Student_ID, QR_Code, Sign_In_Time) VALUES (?, ?, ?)");
        $stmtInsert->execute([$studentId, $scannedQrCode, $currentTime]);
        $message = "Student $studentId signed in successfully.";
    }
    ob_end_clean();
    echo json_encode(['success' => true, 'message' => $message]);
    exit;
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage() . " | QR Code: " . $scannedQrCode);
    // TODO: implement specific error handling for sign-in/out conflicts or business rules
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'You can\'t sign in or out at this time.']);
    exit;
} catch (Exception $e) {
    error_log("General Error: " . $e->getMessage() . " | QR Code: " . $scannedQrCode);
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Unexpected error occurred.']);
    exit;
}

// Fallback - Should not be reached if exit; is used everywhere
ob_end_clean();
echo json_encode(['success' => false, 'message' => 'An unknown server error occurred.']);
?>
