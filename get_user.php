<?php
header('Content-Type: application/json');
include 'config.php';

$id = $_GET['id'] ?? 0;
$stmt = $conn->prepare("SELECT id, name, role FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
if ($user = $result->fetch_assoc()) {
    echo json_encode($user);
} else {
    echo json_encode(['success' => false, 'message' => 'User tidak ditemukan']);
}
$stmt->close();
$conn->close();
?>
