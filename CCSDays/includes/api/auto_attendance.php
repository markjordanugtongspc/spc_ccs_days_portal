<?php
date_default_timezone_set('Asia/Manila');
require_once __DIR__ . '/../config.php';

header('Content-Type: application/json');

try {
  $pdo = getDbConnection();

  // Get event ID from query parameter or use default
  $eventId = isset($_GET['event_id']) ? $_GET['event_id'] : null;

  if (!$eventId) {
    // Get the most recent approved event
    $stmtEvent = $pdo->prepare("SELECT id FROM events WHERE status = 'approved' ORDER BY event_date DESC LIMIT 1");
    $stmtEvent->execute();
    $event = $stmtEvent->fetch(PDO::FETCH_ASSOC);
    $eventId = $event ? $event['id'] : null;
  }

  if (!$eventId) {
    echo json_encode(['success' => false, 'message' => 'No active event found']);
    exit;
  }

  // List of students to process automatically
  $autoSignStudents = ['2022-00752', '2022-00769', '2021-01066', '2022-00008', '2022-01308'];

  $results = [];
  foreach ($autoSignStudents as $studentId) {
    $result = processStudentAttendance($studentId, $eventId, $pdo);
    $results[$studentId] = $result;
  }

  echo json_encode([
    'success' => true,
    'message' => 'Automatically processed ' . count($autoSignStudents) . ' students',
    'results' => $results,
    'event_id' => $eventId
  ]);
} catch (Exception $e) {
  echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}

// Include the processStudentAttendance function from above
function processStudentAttendance($studentId, $eventId, $pdo)
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
    if ($isSignInTime) {
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
        // Sign in new entry
        $uniqueQrCode = $studentId . '-' . time();
        $stmtInsert = $pdo->prepare("INSERT INTO attendance (Student_ID, QR_Code, Sign_In_Time, Event_ID) VALUES (?, ?, ?, ?)");
        $stmtInsert->execute([$studentId, $uniqueQrCode, $manilaNow->format('Y-m-d H:i:s'), $eventId]);
        return ['success' => true, 'action' => 'signin', 'message' => "Student $studentId signed in automatically"];
      }
    } else if ($isSignOutTime) {
      // Check if signed in but not signed out
      if ($existingAttendance && !$existingAttendance['Sign_Out_Time']) {
        // Sign out existing entry
        $stmtUpdate = $pdo->prepare("UPDATE attendance SET Sign_Out_Time = ? WHERE Attendance_ID = ?");
        $stmtUpdate->execute([$manilaNow->format('Y-m-d H:i:s'), $existingAttendance['Attendance_ID']]);
        return ['success' => true, 'action' => 'signout', 'message' => "Student $studentId signed out automatically"];
      } else if (!$existingAttendance) {
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
