<?php
session_start();
require_once '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Check if user has admin role
if ($_SESSION['user_role'] !== 'admin') {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get student ID from request
$student_id = isset($_GET['id']) ? $_GET['id'] : null;

if (!$student_id) {
    echo json_encode(['error' => 'Missing student ID']);
    exit;
}

// Prepare query to fetch student details
$query = "SELECT Student_ID, Name, Year, College, Course, Gender, Attendance FROM students WHERE Student_ID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $student = $result->fetch_assoc();
    echo json_encode($student);
} else {
    echo json_encode(['error' => 'Student not found']);
}

// Close connections
$stmt->close();
$conn->close(); 