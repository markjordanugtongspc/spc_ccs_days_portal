<?php
session_start();
header('Content-Type: application/json');
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Not authorized']);
    exit;
}

// Initialize the response array
$response = [
    'success' => false,
    'message' => '',
    'student' => null,
    'students' => []
];

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get the action from the form
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    // Handle different actions
    switch ($action) {
        case 'get':
            // Get student details
            getStudent($conn, $response);
            break;
            
        case 'add':
            // Add a new student
            addStudent($conn, $response);
            break;
            
        case 'update':
            // Update an existing student
            updateStudent($conn, $response);
            break;
            
        case 'delete':
            // Delete a student
            deleteStudent($conn, $response);
            break;
            
        case 'filter_by_year':
            // Filter students by year
            filterStudentsByYear($conn, $response);
            break;
            
        default:
            // Invalid action
            $response['message'] = 'Invalid action specified.';
            break;
    }
} else {
    // Not a POST request
    $response['message'] = 'Invalid request method.';
}

// Return the response as JSON
echo json_encode($response);
$conn->close();

/**
 * Get student details
 */
function getStudent($conn, &$response) {
    // Get the student ID from the form
    $studentId = isset($_POST['student_id']) ? $_POST['student_id'] : '';
    
    if (empty($studentId)) {
        $response['message'] = 'Student ID is required.';
        return;
    }
    
    // Prepare and execute the query
    $stmt = $conn->prepare("SELECT * FROM students WHERE Student_ID = ?");
    $stmt->bind_param("s", $studentId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        // Student found
        $response['success'] = true;
        $response['student'] = $result->fetch_assoc();
        $response['message'] = 'Student found.';
    } else {
        // Student not found
        $response['message'] = 'Student not found.';
    }
}

/**
 * Add a new student
 */
function addStudent($conn, &$response) {
    // Get student data from the form
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $studentId = isset($_POST['student_id']) ? $_POST['student_id'] : '';
    $year = isset($_POST['year']) ? $_POST['year'] : '';
    $college = isset($_POST['college']) ? $_POST['college'] : '';
    $gender = isset($_POST['gender']) ? $_POST['gender'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $phone = isset($_POST['phone']) ? $_POST['phone'] : '';
    $status = isset($_POST['status']) ? $_POST['status'] : 'Active';
    $attendance = isset($_POST['attendance']) ? (int)$_POST['attendance'] : 0;
    
    // Validate required fields
    if (empty($name) || empty($studentId) || empty($year) || empty($gender)) {
        $response['message'] = 'Name, Student ID, Year, and Gender are required.';
        return;
    }
    
    // Check if student ID already exists
    $checkStmt = $conn->prepare("SELECT Student_ID FROM students WHERE Student_ID = ?");
    $checkStmt->bind_param("s", $studentId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows > 0) {
        $response['message'] = 'Student ID already exists.';
        return;
    }
    
    // Prepare and execute the insert query
    $stmt = $conn->prepare("INSERT INTO students (Student_ID, Name, Year, College, Gender, Email, Phone, Status, Attendance) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssi", $studentId, $name, $year, $college, $gender, $email, $phone, $status, $attendance);
    
    if ($stmt->execute()) {
        // Student added successfully
        $response['success'] = true;
        $response['message'] = 'Student added successfully.';
        
        // Get the newly added student
        getStudent($conn, $response);
    } else {
        // Error adding student
        $response['message'] = 'Error adding student: ' . $stmt->error;
    }
}

/**
 * Update an existing student
 */
function updateStudent($conn, &$response) {
    // Get student data from the form
    $studentId = isset($_POST['student_id']) ? $_POST['student_id'] : '';
    $name = isset($_POST['name']) ? $_POST['name'] : '';
    $year = isset($_POST['year']) ? $_POST['year'] : '';
    $college = isset($_POST['college']) ? $_POST['college'] : '';
    $gender = isset($_POST['gender']) ? $_POST['gender'] : '';
    $email = isset($_POST['email']) ? $_POST['email'] : '';
    $phone = isset($_POST['phone']) ? $_POST['phone'] : '';
    $status = isset($_POST['status']) ? $_POST['status'] : '';
    $attendance = isset($_POST['attendance']) ? (int)$_POST['attendance'] : 0;
    
    // Validate required fields
    if (empty($studentId) || empty($name) || empty($year) || empty($gender)) {
        $response['message'] = 'Student ID, Name, Year, and Gender are required.';
        return;
    }
    
    // Check if student exists
    $checkStmt = $conn->prepare("SELECT Student_ID FROM students WHERE Student_ID = ?");
    $checkStmt->bind_param("s", $studentId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        $response['message'] = 'Student not found.';
        return;
    }
    
    // Prepare and execute the update query
    $stmt = $conn->prepare("UPDATE students SET Name = ?, Year = ?, College = ?, Gender = ?, Email = ?, Phone = ?, Status = ?, Attendance = ? WHERE Student_ID = ?");
    $stmt->bind_param("sssssssss", $name, $year, $college, $gender, $email, $phone, $status, $attendance, $studentId);
    
    if ($stmt->execute()) {
        // Student updated successfully
        $response['success'] = true;
        $response['message'] = 'Student updated successfully.';
        
        // Get the updated student
        getStudent($conn, $response);
    } else {
        // Error updating student
        $response['message'] = 'Error updating student: ' . $stmt->error;
    }
}

/**
 * Delete a student
 */
function deleteStudent($conn, &$response) {
    // Get the student ID from the form
    $studentId = isset($_POST['student_id']) ? $_POST['student_id'] : '';
    
    if (empty($studentId)) {
        $response['message'] = 'Student ID is required.';
        return;
    }
    
    // Check if student exists
    $checkStmt = $conn->prepare("SELECT Student_ID FROM students WHERE Student_ID = ?");
    $checkStmt->bind_param("s", $studentId);
    $checkStmt->execute();
    $checkResult = $checkStmt->get_result();
    
    if ($checkResult->num_rows === 0) {
        $response['message'] = 'Student not found.';
        return;
    }
    
    // Prepare and execute the delete query
    $stmt = $conn->prepare("DELETE FROM students WHERE Student_ID = ?");
    $stmt->bind_param("s", $studentId);
    
    if ($stmt->execute()) {
        // Student deleted successfully
        $response['success'] = true;
        $response['message'] = 'Student deleted successfully.';
    } else {
        // Error deleting student
        $response['message'] = 'Error deleting student: ' . $stmt->error;
    }
}

/**
 * Filter students by year level
 */
function filterStudentsByYear($conn, &$response) {
    // Get the year from the form
    $year = isset($_POST['year']) ? $_POST['year'] : '';
    
    // Map year labels to database values
    if ($year === '1st') $year = '1';
    else if ($year === '2nd') $year = '2';
    else if ($year === '3rd') $year = '3';
    else if ($year === '4th') $year = '4';
    
    // Prepare the query
    $query = "SELECT * FROM students";
    
    // If a specific year is provided, filter by it
    if (!empty($year) && $year !== 'all') {
        $query .= " WHERE Year = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $year);
    } else {
        // Get all students
        $stmt = $conn->prepare($query);
    }
    
    // Execute the query
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Fetch all students
    $students = [];
    while ($row = $result->fetch_assoc()) {
        $students[] = $row;
    }
    
    // Return the students
    $response['success'] = true;
    $response['students'] = $students;
    $response['message'] = count($students) . ' students found.';
} 