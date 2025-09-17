<?php
// Password verification test script

require_once 'includes/config.php';

// Test user credentials
$email = 'admin@gmail.com'; // Use an existing email from your database
$testPassword = '09269958724'; // Replace with the password you used when registering

echo "<h2>Password Verification Test</h2>";

try {
    // Retrieve the user from the database
    $stmt = $conn->prepare("SELECT * FROM staff WHERE email = ?");
    if (!$stmt) {
        throw new Exception("Database error: " . $conn->error);
    }
    
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        echo "<p>User found: " . htmlspecialchars($user['name']) . "</p>";
        echo "<p>Email: " . htmlspecialchars($user['email']) . "</p>";
        echo "<p>Stored password hash: " . htmlspecialchars(substr($user['password'], 0, 20)) . "...</p>";
        
        // Test password verification
        $isPasswordValid = password_verify($testPassword, $user['password']);
        
        echo "<p>Testing password verification...</p>";
        echo "<p>Result: " . ($isPasswordValid ? "SUCCESS! Password is valid." : "FAILURE! Password is invalid.") . "</p>";
        
        // Let's try to create a new hash with the same password and verify it
        echo "<h3>Creating a new hash with the same password:</h3>";
        $newHash = password_hash($testPassword, PASSWORD_DEFAULT);
        echo "<p>New hash: " . htmlspecialchars(substr($newHash, 0, 20)) . "...</p>";
        
        $verifyNewHash = password_verify($testPassword, $newHash);
        echo "<p>Verifying with new hash: " . ($verifyNewHash ? "SUCCESS!" : "FAILURE!") . "</p>";
        
        // Check if the BCRYPT algorithm is being used
        echo "<p>Hash algorithm info: " . (strpos($user['password'], '$2y$') === 0 ? "BCRYPT detected" : "Not using BCRYPT") . "</p>";
        
    } else {
        echo "<p>User not found with email: " . htmlspecialchars($email) . "</p>";
    }

    $stmt->close();
    $conn->close();
    
} catch (Exception $e) {
    echo "<p>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?> 