<?php
header('Content-Type: application/json');
include 'config.php';

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'] ?? 0;

$stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal menghapus user!']);
}
$stmt->close();
$conn->close();
?>
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $data['id']);
    
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'User deleted successfully'
        ]);
    } else {
        throw new Exception("Failed to delete user");
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}

$conn->close();
?>
