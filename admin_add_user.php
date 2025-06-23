<?php
header('Content-Type: application/json');
include 'config.php';

try {
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $role = $_POST['role'];
    
    if (empty($username) || empty($password)) {
        throw new Exception("Semua field harus diisi!");
    }
    
    $stmt = $conn->prepare("SELECT id FROM users WHERE name = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        throw new Exception("Username sudah digunakan!");
    }
    
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO users (name, password, role) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $hashedPassword, $role);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception("Gagal menambahkan user!");
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
