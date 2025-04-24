<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include database configuration
require_once '../config.php';

// Set headers for JSON response
header('Content-Type: application/json');

// Handle CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');

// Handle preflight OPTIONS request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Log request details
error_log("API Request: " . $_SERVER['REQUEST_METHOD'] . " " . $_SERVER['REQUEST_URI']);

// Connect to database
$conn = new mysqli($servername, $username, $password, $database, $port);

// Check connection
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['error' => 'Database connection failed: ' . $conn->connect_error]);
    exit;
}

// Check if events table exists, create if it doesn't
ensureEventsTableExists($conn);

/**
 * Check if events table exists and create it if it doesn't
 */
function ensureEventsTableExists($conn) {
    // Check if table exists
    $result = $conn->query("SHOW TABLES LIKE 'events'");
    
    if ($result && $result->num_rows == 0) {
        // Table doesn't exist, create it
        $sql = "CREATE TABLE `events` (
            `id` INT AUTO_INCREMENT PRIMARY KEY,
            `name` VARCHAR(255) NOT NULL,
            `event_date` DATETIME NOT NULL,
            `venue` VARCHAR(255) NOT NULL,
            `description` TEXT,
            `reminder_enabled` BOOLEAN DEFAULT FALSE,
            `reminder_time` VARCHAR(10),
            `status` ENUM('pending', 'approved') DEFAULT 'pending',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        
        if ($conn->query($sql) === TRUE) {
            error_log("Events table created successfully");
        } else {
            error_log("Error creating events table: " . $conn->error);
        }
    }
}

// Get request method
$method = $_SERVER['REQUEST_METHOD'];
error_log("Processing request method: $method");

// Handle different HTTP methods
switch ($method) {
    case 'GET':
        // Get events (with optional filters)
        getEvents($conn);
        break;
    case 'POST':
        // Create new event
        createEvent($conn);
        break;
    case 'PUT':
        // Update existing event
        updateEvent($conn);
        break;
    case 'DELETE':
        // Delete event
        deleteEvent($conn);
        break;
    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
}

// Close database connection
$conn->close();

/**
 * Get events from database
 */
function getEvents($conn) {
    // Check if specific event ID is requested
    if (isset($_GET['id'])) {
        $id = $conn->real_escape_string($_GET['id']);
        $sql = "SELECT * FROM events WHERE id = '$id'";
    } else {
        // Get all events, optionally filtered by status
        $status = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : null;
        $sql = "SELECT * FROM events";
        
        if ($status) {
            $sql .= " WHERE status = '$status'";
        }
        
        $sql .= " ORDER BY event_date DESC";
    }
    
    $result = $conn->query($sql);
    
    if ($result) {
        $events = [];
        while ($row = $result->fetch_assoc()) {
            // Format date for display
            $dateObj = new DateTime($row['event_date']);
            $row['formatted_date'] = $dateObj->format('Y-m-d • g:i A');
            
            $events[] = $row;
        }
        
        echo json_encode(['success' => true, 'data' => $events]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to fetch events: ' . $conn->error]);
    }
}

/**
 * Create new event
 */
function createEvent($conn) {
    // Get JSON data from request body
    $jsonInput = file_get_contents('php://input');
    error_log("Create Event JSON Input: " . $jsonInput);
    
    $data = json_decode($jsonInput, true);
    
    // Log decoded data
    error_log("Decoded data: " . print_r($data, true));
    
    // Validate required fields
    if (!isset($data['name']) || !isset($data['date']) || !isset($data['venue'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields', 'received' => $data]);
        return;
    }
    
    // Sanitize input
    $name = $conn->real_escape_string($data['name']);
    $date = $conn->real_escape_string($data['date']);
    $venue = $conn->real_escape_string($data['venue']);
    $description = isset($data['description']) ? $conn->real_escape_string($data['description']) : '';
    $reminderEnabled = isset($data['reminder']) && $data['reminder'] ? 1 : 0;
    $reminderTime = isset($data['reminderOption']) ? $conn->real_escape_string($data['reminderOption']) : null;
    
    // Log the date format received
    error_log("Original date format: $date");
    
    // Format the date properly for MySQL
    try {
        // Handle different date formats
        if (strpos($date, 'T') !== false) {
            // Format: YYYY-MM-DDTHH:MM (from datetime-local input)
            $dateObj = new DateTime($date);
        } else {
            // Try to parse other formats
            $dateObj = new DateTime($date);
        }
        
        $formattedDate = $dateObj->format('Y-m-d H:i:s');
        error_log("Formatted date: $formattedDate");
    } catch (Exception $e) {
        error_log("Date parsing error: " . $e->getMessage());
        $formattedDate = $date; // Use original if parsing fails
        error_log("Using original date: $formattedDate");
    }
    
    // Insert event into database
    $sql = "INSERT INTO events (name, event_date, venue, description, reminder_enabled, reminder_time, status)
            VALUES ('$name', '$formattedDate', '$venue', '$description', $reminderEnabled, " .
            ($reminderTime ? "'$reminderTime'" : "NULL") . ", 'pending')";
    
    // Log SQL query
    error_log("SQL Query: " . $sql);
    
    // Execute query with error handling
    try {
        $result = $conn->query($sql);
        
        if ($result === TRUE) {
            $eventId = $conn->insert_id;
            error_log("Event created with ID: " . $eventId);
            
            // Get the newly created event
            $selectSql = "SELECT * FROM events WHERE id = $eventId";
            error_log("Select SQL: " . $selectSql);
            
            $selectResult = $conn->query($selectSql);
            
            if ($selectResult && $selectResult->num_rows > 0) {
                $event = $selectResult->fetch_assoc();
                
                // Format date for display
                $dateObj = new DateTime($event['event_date']);
                $event['formatted_date'] = $dateObj->format('Y-m-d • g:i A');
                
                echo json_encode(['success' => true, 'message' => 'Event created successfully', 'data' => $event]);
            } else {
                error_log("Failed to fetch created event: " . $conn->error);
                echo json_encode(['success' => true, 'message' => 'Event created successfully, but could not fetch details']);
            }
        } else {
            error_log("Database error: " . $conn->error);
            http_response_code(500);
            echo json_encode(['error' => 'Failed to create event: ' . $conn->error]);
        }
    } catch (Exception $e) {
        error_log("Exception during query execution: " . $e->getMessage());
        http_response_code(500);
        echo json_encode(['error' => 'Exception during event creation: ' . $e->getMessage()]);
    }
}

/**
 * Update existing event
 */
function updateEvent($conn) {
    // Get JSON data from request body
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if (!isset($data['id']) || !isset($data['name']) || !isset($data['date']) || !isset($data['venue'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing required fields']);
        return;
    }
    
    // Sanitize input
    $id = $conn->real_escape_string($data['id']);
    $name = $conn->real_escape_string($data['name']);
    $date = $conn->real_escape_string($data['date']);
    $venue = $conn->real_escape_string($data['venue']);
    $description = isset($data['description']) ? $conn->real_escape_string($data['description']) : '';
    $reminderEnabled = isset($data['reminder']) && $data['reminder'] ? 1 : 0;
    $reminderTime = isset($data['reminderOption']) ? $conn->real_escape_string($data['reminderOption']) : null;
    $status = isset($data['status']) ? $conn->real_escape_string($data['status']) : 'pending';
    
    // Update event in database
    $sql = "UPDATE events SET 
            name = '$name', 
            event_date = '$date', 
            venue = '$venue', 
            description = '$description', 
            reminder_enabled = $reminderEnabled, 
            reminder_time = " . ($reminderTime ? "'$reminderTime'" : "NULL") . ",
            status = '$status'
            WHERE id = $id";
    
    if ($conn->query($sql) === TRUE) {
        // Get the updated event
        $sql = "SELECT * FROM events WHERE id = $id";
        $result = $conn->query($sql);
        $event = $result->fetch_assoc();
        
        // Format date for display
        $dateObj = new DateTime($event['event_date']);
        $event['formatted_date'] = $dateObj->format('Y-m-d • g:i A');
        
        echo json_encode(['success' => true, 'message' => 'Event updated successfully', 'data' => $event]);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to update event: ' . $conn->error]);
    }
}

/**
 * Delete event
 */
function deleteEvent($conn) {
    // Get JSON data from request body
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Validate required fields
    if (!isset($data['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'Missing event ID']);
        return;
    }
    
    // Sanitize input
    $id = $conn->real_escape_string($data['id']);
    
    // Delete event from database
    $sql = "DELETE FROM events WHERE id = $id";
    
    if ($conn->query($sql) === TRUE) {
        echo json_encode(['success' => true, 'message' => 'Event deleted successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to delete event: ' . $conn->error]);
    }
}