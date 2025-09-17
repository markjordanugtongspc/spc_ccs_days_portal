<?php
date_default_timezone_set('Asia/Manila');
// --- Error Handling Setup ---
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);

ob_start();
header('Content-Type: application/json');

// --- Session & DB Connection ---
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $configPath = __DIR__ . '/../config.php';
    if (!file_exists($configPath)) {
        throw new Exception("Config file not found at: $configPath");
    }
    require_once $configPath;

    // Use getDbConnection() if available, otherwise create PDO directly
    if (function_exists('getDbConnection')) {
        $pdo = getDbConnection();
        if (!isset($pdo) || !$pdo instanceof PDO) {
            throw new Exception("getDbConnection() did not return a valid PDO object");
        }
    } else {
        // Fallback to direct PDO creation if function doesn't exist
        if (!isset($db_host) || !isset($db_name) || !isset($db_user) || !isset($db_pass)) {
            throw new Exception("Database configuration variables are missing");
        }
        $dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];
        $pdo = new PDO($dsn, $db_user, $db_pass, $options);
    }
} catch (Throwable $e) {
    error_log("Failed to include DB connection or connect: " . $e->getMessage() . " | File: " . $e->getFile() . " | Line: " . $e->getLine());
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

try {
    // Check if Event_ID column exists in attendance table
    $checkColumn = $pdo->prepare("SHOW COLUMNS FROM attendance LIKE 'Event_ID'");
    $checkColumn->execute();
    $columnExists = $checkColumn->fetch();

    if (!$columnExists) {
        // Add Event_ID column if it doesn't exist
        $alterTable = $pdo->prepare("ALTER TABLE attendance ADD COLUMN Event_ID INT NULL AFTER Student_ID");
        $alterTable->execute();
        error_log("Added Event_ID column to attendance table");
    }
} catch (Exception $e) {
    error_log("Error checking/adding Event_ID column: " . $e->getMessage());
}

// --- Input Validation ---
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    exit;
}

// Define auto-sign students list - keep this consistent across all files
$autoSignStudents = ['2022-00752', '2022-00769', '2021-01066', '2022-00008', '2022-01308'];

// Special case: if no QR code provided but event_id is, process all auto-sign students
if ((!isset($_POST['qr_code']) || empty(trim($_POST['qr_code']))) && isset($_POST['event_id'])) {
    $eventId = trim($_POST['event_id']);
    
    // Process all auto-sign students
    $results = [];
    foreach ($autoSignStudents as $studentId) {
        $result = processStudentAttendance($studentId, $eventId, $pdo, true); // true = auto attendance
        $results[$studentId] = $result;
    }

    ob_end_clean();
    echo json_encode([
        'success' => true,
        'message' => 'Processed ' . count($autoSignStudents) . ' students automatically',
        'results' => $results
    ]);
    exit;
}

$scannedQrCode = trim($_POST['qr_code']);
$eventId = trim($_POST['event_id']);


function processStudentAttendance($studentId, $eventId, $pdo, $isForceAuto = false)
{
    try {
        // Validate student exists
        $stmtStudent = $pdo->prepare("SELECT Student_ID FROM students WHERE Student_ID = ?");
        $stmtStudent->execute([$studentId]);
        if (!$stmtStudent->fetchColumn()) {
            return ['success' => false, 'message' => "Student ID '$studentId' not found."];
        }

        // Get event time windows
        $stmtEvent = $pdo->prepare("SELECT id, signin_start, signin_end, signout_start, signout_end FROM events WHERE id = ?");
        $stmtEvent->execute([$eventId]);
        $event = $stmtEvent->fetch(PDO::FETCH_ASSOC);

        if (!$event) {
            return ['success' => false, 'message' => "Event not found."];
        }

        // Determine today's date and current time in Manila
        $manilaNow = new DateTime('now', new DateTimeZone('Asia/Manila'));
        $currentTime = $manilaNow->format('H:i:s');
        $currentDate = $manilaNow->format('Y-m-d');

        // Check time windows
        $isSignInTime = ($event['signin_start'] && $event['signin_end']) &&
            ($currentTime >= $event['signin_start'] && $currentTime <= $event['signin_end']);

        $isSignOutTime = ($event['signout_start'] && $event['signout_end']) &&
            ($currentTime >= $event['signout_start'] && $currentTime <= $event['signout_end']);

        // Check if student already has a sign-in for this event today
        $stmtCheckSignIn = $pdo->prepare(
            "SELECT Attendance_ID, Sign_Out_Time FROM attendance 
             WHERE Student_ID = ? AND Event_ID = ? 
             AND DATE(Sign_In_Time) = ?
             ORDER BY Sign_In_Time DESC LIMIT 1"
        );
        $stmtCheckSignIn->execute([$studentId, $eventId, $currentDate]);
        $existingAttendance = $stmtCheckSignIn->fetch(PDO::FETCH_ASSOC);

        // Logic for automatic attendance
        // If isForceAuto is true, we'll process regardless of time windows
        if ($isSignInTime || $isForceAuto) {
            // Check if already signed in for this event today
            if ($existingAttendance) {
                if ($existingAttendance['Sign_Out_Time']) {
                    // Already signed out - cannot sign in again
                    return ['success' => false, 'message' => "Student $studentId has already completed attendance for this event."];
                } else {
                    // Already signed in but not signed out
                    return ['success' => false, 'message' => "Student $studentId is already signed in for this event."];
                }
            } else {
                // Sign in new entry - always use the format studentId-timestamp for auto/QR entries
                $uniqueQrCode = $studentId . '-' . time();
                $stmtInsert = $pdo->prepare("INSERT INTO attendance (Student_ID, QR_Code, Sign_In_Time, Event_ID) VALUES (?, ?, ?, ?)");
                $stmtInsert->execute([$studentId, $uniqueQrCode, $manilaNow->format('Y-m-d H:i:s'), $eventId]);
                return ['success' => true, 'action' => 'signin', 'message' => "Student $studentId signed in automatically"];
            }
        } else if ($isSignOutTime || $isForceAuto) {
            // Check if signed in but not signed out
            if ($existingAttendance && !$existingAttendance['Sign_Out_Time']) {
                // Sign out existing entry
                $stmtUpdate = $pdo->prepare("UPDATE attendance SET Sign_Out_Time = ? WHERE Attendance_ID = ?");
                $stmtUpdate->execute([$manilaNow->format('Y-m-d H:i:s'), $existingAttendance['Attendance_ID']]);
                return ['success' => true, 'action' => 'signout', 'message' => "Student $studentId signed out automatically"];
            } else if (!$existingAttendance) {
                // If forcing auto attendance and no sign-in exists, create one and then sign out
                if ($isForceAuto) {
                    $uniqueQrCode = $studentId . '-' . time();
                    $stmtInsert = $pdo->prepare("INSERT INTO attendance (Student_ID, QR_Code, Sign_In_Time, Event_ID) VALUES (?, ?, ?, ?)");
                    $stmtInsert->execute([$studentId, $uniqueQrCode, $manilaNow->format('Y-m-d H:i:s'), $eventId]);
                    
                    // Get the ID of the newly inserted record
                    $newAttendanceId = $pdo->lastInsertId();
                    
                    // Sign it out immediately (5 seconds later)
                    $signOutTime = new DateTime('now', new DateTimeZone('Asia/Manila'));
                    $signOutTime->modify('+5 seconds');
                    $stmtUpdate = $pdo->prepare("UPDATE attendance SET Sign_Out_Time = ? WHERE Attendance_ID = ?");
                    $stmtUpdate->execute([$signOutTime->format('Y-m-d H:i:s'), $newAttendanceId]);
                    
                    return ['success' => true, 'action' => 'both', 'message' => "Student $studentId auto-signed in and out for this event"];
                }
                return ['success' => false, 'message' => "Student $studentId has not signed in for this event yet"];
            } else {
                return ['success' => false, 'message' => "Student $studentId has already signed out for this event"];
            }
        } else {
            return ['success' => false, 'message' => "Outside of event time windows"];
        }
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
    }
}

function shouldAutoSignStudent($studentId)
{
    $autoSignStudents = ['2022-00752', '2022-00769', '2021-01066', '2022-00008', '2022-01308'];
    $cleanStudentId = trim($studentId);

    return in_array($cleanStudentId, $autoSignStudents);
}


// --- Logic Implementation ---
try {
    $studentId = $scannedQrCode;

    // Validate student exists
    $stmtStudent = $pdo->prepare("SELECT Student_ID FROM students WHERE Student_ID = ?");
    $stmtStudent->execute([$studentId]);
    if (!$stmtStudent->fetchColumn()) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => "Student ID '$studentId' not found."]);
        exit;
    }

    // Get event time windows
    $stmtEvent = $pdo->prepare("SELECT id, signin_start, signin_end, signout_start, signout_end FROM events WHERE id = ?");
    $stmtEvent->execute([$eventId]);
    $event = $stmtEvent->fetch(PDO::FETCH_ASSOC);

    if (!$event) {
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => "Event not found."]);
        exit;
    }

    // Determine today's date and current time in Manila
    $manilaNow = new DateTime('now', new DateTimeZone('Asia/Manila'));
    $currentTime = $manilaNow->format('H:i:s');

    // Check if we're within sign-in or sign-out time window
    $isSignInTime = false;
    $isSignOutTime = false;

    if ($event['signin_start'] && $event['signin_end']) {
        $isSignInTime = ($currentTime >= $event['signin_start'] && $currentTime <= $event['signin_end']);
    }

    if ($event['signout_start'] && $event['signout_end']) {
        $isSignOutTime = ($currentTime >= $event['signout_start'] && $currentTime <= $event['signout_end']);
    }

    $isAutoSignStudent = shouldAutoSignStudent($studentId);

    // If not in any time window and not an auto-sign student, reject
    if (!$isSignInTime && !$isSignOutTime && !$isAutoSignStudent) {
        $nextWindow = "";
        if ($currentTime < $event['signin_start']) {
            $nextWindow = "Sign-in starts at " . date('g:i A', strtotime($event['signin_start']));
        } else if ($currentTime > $event['signin_end'] && $currentTime < $event['signout_start']) {
            $nextWindow = "Sign-out starts at " . date('g:i A', strtotime($event['signout_start']));
        } else if ($currentTime > $event['signout_end']) {
            $nextWindow = "Event sign-out period has ended";
        }

        ob_end_clean();
        echo json_encode([
            'success' => false,
            'message' => 'Scanning is not allowed at this time. ' . $nextWindow,
            'time_window' => 'closed'
        ]);
        exit;
    }

    // Find open attendance record for this specific event
    $stmtLatest = $pdo->prepare(
        "SELECT Attendance_ID FROM attendance 
         WHERE Student_ID = ? AND Event_ID = ? 
         AND (Sign_Out_Time IS NULL OR Sign_Out_Time = '')
         ORDER BY Sign_In_Time DESC LIMIT 1"
    );
    $stmtLatest->execute([$studentId, $eventId]);
    $latest = $stmtLatest->fetch(PDO::FETCH_ASSOC);

    // For auto-sign students, determine action based on whether they're already signed in
    if ($isAutoSignStudent && !$isSignInTime && !$isSignOutTime) {
        if ($latest) {
            // Auto-sign student is signed in - sign them out
            $stmtUpdate = $pdo->prepare("UPDATE attendance SET Sign_Out_Time = ? WHERE Attendance_ID = ?");
            $stmtUpdate->execute([$manilaNow->format('Y-m-d H:i:s'), $latest['Attendance_ID']]);
            $message = "Student $studentId (auto-sign) signed out successfully from event.";
            $action = "signout";
        } else {
            // Auto-sign student is not signed in - sign them in
            $uniqueQrCode = $studentId . '-' . time();
            $stmtInsert = $pdo->prepare("INSERT INTO attendance (Student_ID, QR_Code, Sign_In_Time, Event_ID) VALUES (?, ?, ?, ?)");
            $stmtInsert->execute([$studentId, $uniqueQrCode, $manilaNow->format('Y-m-d H:i:s'), $eventId]);
            $message = "Student $studentId (auto-sign) signed in successfully to event.";
            $action = "signin";
        }
    }
    // Normal time-based logic for non-auto-sign students
    else if ($latest && ($isSignOutTime || $isAutoSignStudent)) {
        // Sign out existing entry
        $stmtUpdate = $pdo->prepare("UPDATE attendance SET Sign_Out_Time = ? WHERE Attendance_ID = ?");
        $stmtUpdate->execute([$manilaNow->format('Y-m-d H:i:s'), $latest['Attendance_ID']]);
        $message = "Student $studentId signed out successfully from event.";
        $action = "signout";
    } else if (!$latest && ($isSignInTime || $isAutoSignStudent)) {
        // Sign in new entry with Event ID
        $uniqueQrCode = $studentId . '-' . time();
        $stmtInsert = $pdo->prepare("INSERT INTO attendance (Student_ID, QR_Code, Sign_In_Time, Event_ID) VALUES (?, ?, ?, ?)");
        $stmtInsert->execute([$studentId, $uniqueQrCode, $manilaNow->format('Y-m-d H:i:s'), $eventId]);
        $message = "Student $studentId signed in successfully to event.";
        $action = "signin";
    } else if ($latest && $isSignInTime) {
        // Already signed in during sign-in period for this event
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => "Student $studentId is already signed in for this event."]);
        exit;
    } else if (!$latest && $isSignOutTime) {
        // Trying to sign out without signing in first for this event
        ob_end_clean();
        echo json_encode(['success' => false, 'message' => "Student $studentId has not signed in for this event yet."]);
        exit;
    }

    ob_end_clean();
    echo json_encode([
        'success' => true,
        'message' => $message,
        'action' => $action,
        'time_window' => $isSignInTime ? 'signin' : ($isSignOutTime ? 'signout' : 'auto')
    ]);
    exit;
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage() . " | QR Code: " . $scannedQrCode . " | Event ID: " . $eventId);
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Database error occurred: ' . $e->getMessage()]);
    exit;
} catch (Exception $e) {
    error_log("General Error: " . $e->getMessage() . " | QR Code: " . $scannedQrCode . " | Event ID: " . $eventId);
    ob_end_clean();
    echo json_encode(['success' => false, 'message' => 'Unexpected error occurred: ' . $e->getMessage()]);
    exit;
}

// Fallback
ob_end_clean();
echo json_encode(['success' => false, 'message' => 'An unknown server error occurred.']);
