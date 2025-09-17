<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "ccs_events_db_2025";
$port = 3306;

// Create connection
$conn = new mysqli($servername, $username, $password, $database, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to ensure proper handling of special characters
$conn->set_charset("utf8mb4");

// Helper function for PDO usage in API endpoints
function getDbConnection()
{
    global $servername, $username, $password, $database, $port;
    $dsn = "mysql:host=$servername;port=$port;dbname=$database;charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    try {
        return new PDO($dsn, $username, $password, $options);
    } catch (PDOException $e) {
        throw $e;
    }
}
