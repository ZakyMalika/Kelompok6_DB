<?php
header('Content-Type: application/json');
include 'config.php';

try {
    $id = $_GET['id'];
    
    $stmt = $conn->prepare("SELECT id, name, role FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($user = $result->fetch_assoc()) {
        echo json_encode($user);
    } else {
        throw new Exception("User tidak ditemukan!");
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
