<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['logged_in'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

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

    $data = json_decode(file_get_contents('php://input'), true);
    
    if (!isset($data['item_id']) || !isset($data['quantity'])) {
        throw new Exception("Invalid request data");
    }

    $itemId = $data['item_id'];
    $quantity = $data['quantity'];
    $userId = $_SESSION['user_id'];

    // Start transaction
    $conn->begin_transaction();

    try {
        // Check current stock and price
        $stmt = $conn->prepare("SELECT stock, price FROM produck WHERE id = ? FOR UPDATE");
        $stmt->bind_param("i", $itemId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            throw new Exception("Produk tidak ditemukan");
        }

        $row = $result->fetch_assoc();
        $currentStock = $row['stock'];
        $price = $row['price'];

        if ($currentStock < $quantity) {
            throw new Exception("Stok tidak mencukupi");
        }

        // Update stock
        $newStock = $currentStock - $quantity;
        $stmt = $conn->prepare("UPDATE produck SET stock = ? WHERE id = ?");
        $stmt->bind_param("ii", $newStock, $itemId);
        
        if (!$stmt->execute()) {
            throw new Exception("Gagal mengupdate stok");
        }

        // Insert order record
        $totalPrice = $price * $quantity;
        $stmt = $conn->prepare("INSERT INTO orders (user_id, product_id, quantity, total_price) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiid", $userId, $itemId, $quantity, $totalPrice);
        
        if (!$stmt->execute()) {
            throw new Exception("Gagal menyimpan pesanan");
        }

        // Commit transaction
        $conn->commit();

        echo json_encode([
            'success' => true,
            'message' => 'Order berhasil diproses',
            'new_stock' => $newStock
        ]);

    } catch (Exception $e) {
        $conn->rollback();
        throw $e;
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
