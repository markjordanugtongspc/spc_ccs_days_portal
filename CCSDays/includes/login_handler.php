<?php
// Start session
session_start();

// Require database connection
require_once 'config.php';

// Set response headers
header('Content-Type: application/json');

// Initialize response array
$response = [
    'success' => false,
    'message' => '',
    'redirect' => ''
];

// Check if request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get login credentials from form
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $remember = isset($_POST['remember']) ? (bool)$_POST['remember'] : false;
    
    // Validate inputs
    if (empty($email)) {
        $response['message'] = 'Email is required';
        echo json_encode($response);
        exit;
    }
    
    if (empty($password)) {
        $response['message'] = 'Password is required';
        echo json_encode($response);
        exit;
    }
    
    // Prepare statement to check if user exists
    $stmt = $conn->prepare("SELECT * FROM staff WHERE email = ?");
    if (!$stmt) {
        $response['message'] = 'Database error: ' . $conn->error;
        echo json_encode($response);
        exit;
    }
    
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    // Check if user exists
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // Verify password
        if (password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            
            // Set remember me cookie if checked
            if ($remember) {
                $cookie_expiry = time() + (30 * 24 * 60 * 60); // 30 days
                setcookie('remember_user', $user['id'], $cookie_expiry, '/');
            }
            
            // Set response to success
            $response['success'] = true;
            $response['redirect'] = 'pages/dashboard.php';
            
            // Log successful login
            error_log("User login successful: " . $email);
        } else {
            // Invalid password
            $response['message'] = 'Invalid password. Please try again.';
            
            // Log failed login attempt
            error_log("Failed login attempt (invalid password): " . $email);
        }
    } else {
        // User not found
        $response['message'] = 'Email not found. Please check your email or register.';
        
        // Log failed login attempt
        error_log("Failed login attempt (email not found): " . $email);
    }
    
    $stmt->close();
} else {
    // Not a POST request
    $response['message'] = 'Invalid request method';
}

// Close database connection
$conn->close();

// Return response as JSON
echo json_encode($response);
