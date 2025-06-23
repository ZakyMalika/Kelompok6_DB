<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['user_type'] !== 'user') {
    header('Location: login.html');
    exit;
}

$host = 'localhost';
$dbname = 'restorant';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) die("Koneksi gagal: " . $conn->connect_error);

$user_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

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
$stmt->close();
$conn->close();
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
        .order-item { transition: transform 0.2s; }
        .order-item:hover { transform: translateY(-5px); }
    </style>
</head>
<body class="bg-light">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-utensils me-2"></i>FoodOrder
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">Menu</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="order_history_page.php">Riwayat Pesanan</a>
                    </li>
                </ul>
                <div class="d-flex align-items-center">
                    <span class="me-3 fw-bold text-primary">Hello, <?= htmlspecialchars($username) ?></span>
                    <a href="logout.php" class="btn btn-outline-danger">
                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container py-5">
        <h2 class="mb-4">Riwayat Pesanan Anda</h2>
        <?php if (!empty($orders)): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead class="table-primary">
                        <tr>
                            <th>Tanggal</th>
                            <th>Nama Produk</th>
                            <th>Gambar</th>
                            <th>Jumlah</th>
                            <th>Total Harga</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                        <tr>
                            <td><?= date('d/m/Y H:i', strtotime($order['order_date'])) ?></td>
                            <td><?= htmlspecialchars($order['product_name']) ?></td>
                            <td>
                                <img src="<?= htmlspecialchars($order['image_url']) ?>" alt="<?= htmlspecialchars($order['product_name']) ?>" style="width:60px; height:60px; object-fit:cover; border-radius:8px;">
                            </td>
                            <td><?= (int)$order['quantity'] ?></td>
                            <td>Rp <?= number_format($order['total_price'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-receipt text-muted" style="font-size: 4rem;"></i>
                <h4 class="mt-3">Belum ada pesanan</h4>
                <p class="text-muted">Anda belum melakukan pemesanan apapun</p>
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-utensils me-2"></i>Pesan Sekarang
                </a>
            </div>
        <?php endif; ?>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
