<?php
header('Content-Type: application/json');
session_start();

$host = 'localhost';
$dbname = 'restorant';
$user = 'root';
$pass = '';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    if (
        !isset($data['user_id']) ||
        !isset($data['product_id']) ||
        !isset($data['quantity'])
    ) {
        throw new Exception("Data tidak lengkap");
    }

    $user_id = intval($data['user_id']);
    $product_id = intval($data['product_id']);
    $quantity = intval($data['quantity']);

    if ($quantity < 1) throw new Exception("Jumlah pesanan tidak valid");

    $conn = new mysqli($host, $user, $pass, $dbname);
    if ($conn->connect_error) throw new Exception("Koneksi gagal: " . $conn->connect_error);

    // Ambil harga produk
    $stmt = $conn->prepare("SELECT price, stock FROM produck WHERE id = ?");
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        if ($row['stock'] < $quantity) throw new Exception("Stok tidak cukup");
        $total_price = $row['price'] * $quantity;
    } else {
        throw new Exception("Produk tidak ditemukan");
    }
    $stmt->close();

    // Simpan order
    $stmt = $conn->prepare("INSERT INTO orders (user_id, product_id, quantity, total_price) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiid", $user_id, $product_id, $quantity, $total_price);
    if (!$stmt->execute()) throw new Exception("Gagal menyimpan pesanan");
    $stmt->close();

    // Update stok produk
    $stmt = $conn->prepare("UPDATE produck SET stock = stock - ? WHERE id = ?");
    $stmt->bind_param("ii", $quantity, $product_id);
    $stmt->execute();
    $stmt->close();

    echo json_encode(['success' => true]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
