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

// Get page number and search term from request
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$search = isset($_GET['search']) ? $_GET['search'] : '';
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 8;

// Calculate offset
$offset = ($page - 1) * $limit;

// Prepare base query
$query = "SELECT Student_ID, Name, Year, College, Course, Gender, Attendance FROM students WHERE 1=1";
$countQuery = "SELECT COUNT(*) as total FROM students WHERE 1=1";

// Add search condition if search term is provided
if (!empty($search)) {
    $searchTerm = "%{$search}%";
    $query .= " AND (Name LIKE ? OR Student_ID LIKE ? OR College LIKE ? OR Course LIKE ?)";
    $countQuery .= " AND (Name LIKE ? OR Student_ID LIKE ? OR College LIKE ? OR Course LIKE ?)";
}

// Add ordering
$query .= " ORDER BY Student_ID DESC LIMIT ? OFFSET ?";

// Prepare and execute count query
$countStmt = $conn->prepare($countQuery);
if (!empty($search)) {
    $countStmt->bind_param("ssss", $searchTerm, $searchTerm, $searchTerm, $searchTerm);
}
$countStmt->execute();
$totalResult = $countStmt->get_result()->fetch_assoc();
$total = $totalResult['total'];

// Prepare and execute main query
$stmt = $conn->prepare($query);
if (!empty($search)) {
    $stmt->bind_param("ssssii", $searchTerm, $searchTerm, $searchTerm, $searchTerm, $limit, $offset);
} else {
    $stmt->bind_param("ii", $limit, $offset);
}
$stmt->execute();
$result = $stmt->get_result();

// Fetch all students
$students = [];
while ($row = $result->fetch_assoc()) {
    $students[] = $row;
}

// Close connections
$stmt->close();
$countStmt->close();
$conn->close();

// Return JSON response
echo json_encode([
    'students' => $students,
    'total' => $total,
    'page' => $page,
    'hasMore' => ($offset + $limit) < $total
]); 