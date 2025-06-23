<?php
header('Content-Type: application/json');
include 'config.php';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'];
    
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        throw new Exception("Gagal menghapus user!");
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
