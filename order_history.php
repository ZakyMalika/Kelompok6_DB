<?php
header('Content-Type: application/json');
session_start();

$host = 'localhost';
$dbname = 'restorant';
$user = 'root';
$pass = '';

try {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['user_id'])) throw new Exception("User tidak valid");

    $user_id = intval($data['user_id']);
    $conn = new mysqli($host, $user, $pass, $dbname);
    if ($conn->connect_error) throw new Exception("Koneksi gagal: " . $conn->connect_error);

    $stmt = $conn->prepare(
        "SELECT o.order_date, p.name AS product_name, p.image_url, o.quantity, o.total_price
         FROM orders o
         JOIN produck p ON o.product_id = p.id
         WHERE o.user_id = ?
         ORDER BY o.order_date DESC"
    );
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
    echo json_encode(['success' => true, 'orders' => $orders]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan - FoodOrder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .order-item {
            transition: transform 0.2s;
        }
        .order-item:hover {
            transform: translateY(-5px);
        }
        .product-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 10px;
        }
    </style>
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.html">
                <i class="fas fa-utensils me-2"></i>FoodOrder
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.html">Menu</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="order_history.php">Riwayat Pesanan</a>
                    </li>
                </ul>
                <div id="navAuthContent">
                    <div class="dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user me-1"></i><?php echo $_SESSION['user_name']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container py-5">
        <h2 class="mb-4">Riwayat Pesanan</h2>
        
        <?php if (!empty($orders)): ?>
            <div class="row g-4">
                <?php foreach ($orders as $order): ?>
                    <div class="col-md-6">
                        <div class="card order-item shadow-sm">
                            <div class="card-body">
                                <div class="d-flex align-items-center">
                                    <img src="<?php echo $order['image_url']; ?>" 
                                         alt="<?php echo $order['product_name']; ?>" 
                                         class="product-image me-3">
                                    <div>
                                        <h5 class="card-title mb-1"><?php echo $order['product_name']; ?></h5>
                                        <p class="text-muted mb-0">
                                            Jumlah: <?php echo $order['quantity']; ?><br>
                                            Total: Rp <?php echo number_format($order['total_price'], 0, ',', '.'); ?><br>
                                            <small>
                                                Tanggal: <?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?>
                                            </small>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-receipt text-muted" style="font-size: 4rem;"></i>
                <h4 class="mt-3">Belum ada pesanan</h4>
                <p class="text-muted">Anda belum melakukan pemesanan apapun</p>
                <a href="index.html" class="btn btn-primary">
                    <i class="fas fa-utensils me-2"></i>Pesan Sekarang
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
