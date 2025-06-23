<?php
header('Content-Type: application/json');

$host = 'localhost';
$dbname = 'restorant';
$user = 'root';
$pass = '';

try {
    $conn = new mysqli($host, $user, $pass, $dbname);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        
        if (empty($username) || empty($password)) {
            throw new Exception("All fields are required!");
        }
        
        // Check if username exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE name = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            throw new Exception("Username already exists!");
        }
        
        // Hash password
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (name, password, role) VALUES (?, ?, 'user')");
        $stmt->bind_param("ss", $username, $hashedPassword);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'User registration successful!'
            ]);
        } else {
            throw new Exception("Failed to save user data!");
        }
        
        $stmt->close();
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>
