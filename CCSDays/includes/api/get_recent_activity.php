<?php
date_default_timezone_set('Asia/Manila');
require_once __DIR__ . '/../../includes/config.php';

header('Content-Type: application/json');

try {
    $pdo = getDbConnection();

    // Get the 6 most recent attendance entries with student details
    $sql = "
        SELECT
            a.Attendance_ID,
            a.Student_ID,
            a.QR_Code,
            a.Sign_In_Time,
            a.Sign_Out_Time,
            s.Name,
            s.Year,
            e.name as Event_Name
        FROM attendance a
        JOIN students s ON a.Student_ID = s.Student_ID
        LEFT JOIN events e ON a.Event_ID = e.id
        ORDER BY a.Attendance_ID DESC
        LIMIT 6
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'entries' => $entries
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Database error: ' . $e->getMessage()
    ]);
}
