<?php
header('Content-Type: application/json');

// Database connection
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
        $role = isset($_POST['role']) ? $_POST['role'] : 'user'; // Default to 'user' if not specified
        
        // Validasi input
        if (empty($username) || empty($password)) {
            throw new Exception("Semua field harus diisi!");
        }
        
        // Cek apakah username sudah ada
        $stmt = $conn->prepare("SELECT id FROM users WHERE name = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            throw new Exception("Username sudah digunakan!");
        }
        
        // Hash password sebelum disimpan
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user baru dengan role
        $stmt = $conn->prepare("INSERT INTO users (name, password, role) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $username, $hashedPassword, $role);
        
        if ($stmt->execute()) {
            echo json_encode([
                'success' => true,
                'message' => 'Registrasi berhasil!'
            ]);
        } else {
            throw new Exception("Gagal menyimpan data user!");
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
