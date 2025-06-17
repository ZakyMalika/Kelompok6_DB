<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.html');
    exit;
}

$host = 'localhost';
$dbname = 'restorant';
$user = 'root';
$pass = '';

try {
    $conn = new mysqli($host, $user, $pass, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }

    // Get all orders with user and product details
    $sql = "SELECT o.*, u.name as user_name, p.name as product_name, p.image_url 
            FROM orders o 
            JOIN users u ON o.user_id = u.id 
            JOIN produck p ON o.product_id = p.id 
            ORDER BY o.order_date DESC";
            
    $result = $conn->query($sql);
    $orders = [];
    while ($row = $result->fetch_assoc()) {
        $orders[] = $row;
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pesanan - Admin Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .sidebar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: white;
        }
        .sidebar .nav-link {
            color: rgba(255, 255, 255, 0.8);
            padding: 15px 20px;
            border-radius: 10px;
            margin: 5px 0;
            transition: all 0.3s;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }
        .main-content {
            background: #f8f9fa;
            min-height: 100vh;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <div class="p-4">
                    <h4 class="text-center mb-4"><i class="fas fa-utensils me-2"></i>FoodOrder</h4>
                    <div class="text-center mb-4">
                        <h6>Selamat Datang</h6>
                        <p class="mb-0"><?php echo $_SESSION['admin_name']; ?></p>
                    </div>
                    <nav class="nav flex-column">
                        <a class="nav-link" href="admin_dashboard.php">
                            <i class="fas fa-box me-2"></i>Kelola Produk
                        </a>
                        <a class="nav-link active" href="#">
                            <i class="fas fa-history me-2"></i>Riwayat Pesanan
                        </a>
                        <a class="nav-link" href="logout.php">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </a>
                    </nav>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content p-4">
                <h2 class="mb-4">Riwayat Pesanan</h2>
                
                <div class="card">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID Order</th>
                                        <th>Pengguna</th>
                                        <th>Produk</th>
                                        <th>Jumlah</th>
                                        <th>Total Harga</th>
                                        <th>Tanggal Order</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($orders as $order): ?>
                                    <tr>
                                        <td>#<?php echo $order['id']; ?></td>
                                        <td><?php echo htmlspecialchars($order['user_name']); ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <img src="<?php echo $order['image_url']; ?>" 
                                                     alt="<?php echo $order['product_name']; ?>" 
                                                     class="product-image me-2">
                                                <?php echo htmlspecialchars($order['product_name']); ?>
                                            </div>
                                        </td>
                                        <td><?php echo $order['quantity']; ?></td>
                                        <td>Rp <?php echo number_format($order['total_price'], 0, ',', '.'); ?></td>
                                        <td><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
