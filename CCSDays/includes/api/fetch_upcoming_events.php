<?php
// Include database configuration
require_once '../config.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Connect to database
$conn = new mysqli($servername, $username, $password, $database, $port);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Check if events table exists
$tableExists = false;
$result = $conn->query("SHOW TABLES LIKE 'events'");
if ($result && $result->num_rows > 0) {
    $tableExists = true;
} else {
    // Table doesn't exist, return empty array
    echo json_encode(['success' => true, 'events' => [], 'message' => 'Events table does not exist']);
    $conn->close();
    exit;
}

// Get current date and time
$currentDateTime = date('Y-m-d H:i:s');

// Fetch upcoming events (both pending and approved) sorted by date
$sql = "SELECT * FROM events
        WHERE event_date >= ?
        ORDER BY event_date ASC
        LIMIT 5";

$stmt = $conn->prepare($sql);
if (!$stmt) {
    echo json_encode(['success' => false, 'error' => 'Failed to prepare statement: ' . $conn->error]);
    $conn->close();
    exit;
}

$stmt->bind_param("s", $currentDateTime);
$stmt->execute();
$result = $stmt->get_result();

$events = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        // Format date for display
        $dateObj = new DateTime($row['event_date']);
        $row['formatted_date'] = $dateObj->format('Y-m-d • g:i A');
        
        $events[] = $row;
    }
}

// Close connection
$stmt->close();
$conn->close();

// Return events as JSON
echo json_encode(['success' => true, 'events' => $events]);