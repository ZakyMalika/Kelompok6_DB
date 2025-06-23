<?php
header('Content-Type: application/json');
include 'config.php';

$id = $_POST['user_id'];
$username = trim($_POST['username']);
$password = $_POST['password'];
$role = $_POST['role'];

if (empty($username)) {
    echo json_encode(['success' => false, 'message' => 'Username tidak boleh kosong!']);
    exit;
}

// Cek username unik
$stmt = $conn->prepare("SELECT id FROM users WHERE name = ? AND id != ?");
$stmt->bind_param("si", $username, $id);
$stmt->execute();
if ($stmt->get_result()->num_rows > 0) {
    echo json_encode(['success' => false, 'message' => 'Username sudah digunakan!']);
    exit;
}
$stmt->close();

if (!empty($password)) {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE users SET name = ?, password = ?, role = ? WHERE id = ?");
    $stmt->bind_param("sssi", $username, $hashedPassword, $role, $id);
} else {
    $stmt = $conn->prepare("UPDATE users SET name = ?, role = ? WHERE id = ?");
    $stmt->bind_param("ssi", $username, $role, $id);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Gagal mengupdate user!']);
}
$stmt->close();
$conn->close();
?>
