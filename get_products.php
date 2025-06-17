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
    
    // Query untuk mengambil semua produk
    $sql = "SELECT id, name, description, price, image_url, category, stock FROM produck WHERE stock > 0 ORDER BY id DESC";
    $result = $conn->query($sql);
    
    $products = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            // Format kategori untuk class CSS
            $categoryClass = $row['category'] === 'MakananBerat' ? 'makanan-berat' : 'makanan-ringan';
            
            $products[] = [
                'id' => $row['id'],
                'name' => $row['name'],
                'description' => $row['description'],
                'price' => number_format($row['price'], 0, ',', '.'),
                'raw_price' => $row['price'],
                'image_url' => $row['image_url'],
                'category' => $row['category'],
                'category_class' => $categoryClass,
                'stock' => $row['stock']
            ];
        }
    }
    
    echo json_encode([
        'success' => true,
        'data' => $products
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>