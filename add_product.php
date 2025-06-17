<?php
session_start();
header('Content-Type: application/json');

// Cek apakah user adalah admin
if (!isset($_SESSION['logged_in']) || $_SESSION['user_type'] !== 'admin') {
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
    
    // Validasi input
    if (empty($_POST['name']) || empty($_POST['description']) || 
        empty($_POST['price']) || empty($_POST['image_url']) || 
        empty($_POST['category']) || empty($_POST['stock'])) {
        throw new Exception("Semua field harus diisi!");
    }
    
    // Prepare statement
    $stmt = $conn->prepare("INSERT INTO produck (name, description, price, image_url, category, stock) VALUES (?, ?, ?, ?, ?, ?)");
    
    // Bind parameters
    $stmt->bind_param("ssdssi", 
        $_POST['name'],
        $_POST['description'],
        $_POST['price'],
        $_POST['image_url'],
        $_POST['category'],
        $_POST['stock']
    );
    
    // Execute query
    if ($stmt->execute()) {
        echo json_encode([
            'success' => true,
            'message' => 'Produk berhasil ditambahkan!',
            'data' => [
                'id' => $conn->insert_id,
                'name' => $_POST['name'],
                'price' => $_POST['price'],
                'category' => $_POST['category']
            ]
        ]);
    } else {
        throw new Exception("Error executing query: " . $stmt->error);
    }
    
    $stmt->close();
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
