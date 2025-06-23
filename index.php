<?php
session_start();

// Koneksi database
$host = 'localhost';
$dbname = 'restorant';
$user = 'root';
$pass = '';
$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) die("Koneksi gagal: " . $conn->connect_error);

// Ambil data produk
$products = [];
$result = $conn->query("SELECT * FROM produck ORDER BY id DESC");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) $products[] = $row;
}

// Cek login user
$isUserLoggedIn = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] && isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'user';
$username = $isUserLoggedIn ? $_SESSION['username'] : null;
$user_id = $isUserLoggedIn ? $_SESSION['user_id'] : null;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FoodOrder - Menu Makanan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <style>
        .hero {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 100px 0;
        }
        .menu-image {
            height: 200px;
            object-fit: cover;
        }
        .menu-item {
            transition: transform 0.3s;
        }
        .menu-item:hover {
            transform: translateY(-5px);
        }
        .category-filter .btn {
            border-radius: 25px;
            padding: 8px 20px;
        }
        .category-filter .btn.active {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border-color: transparent;
            color: white;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fas fa-utensils me-2"></i>FoodOrder
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#menu">Menu</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#about">Tentang</a>
                    </li>
                    <li class="nav-item">
                        <?php if ($isUserLoggedIn): ?>
                            <a class="nav-link" href="order_history_page.php">
                                <i class="fas fa-history me-1"></i>Riwayat Pesanan
                            </a>
                        <?php else: ?>
                            <a class="nav-link" href="#" onclick="showLoginRequiredModal(event)">
                                <i class="fas fa-history me-1"></i>Riwayat Pesanan
                            </a>
                        <?php endif; ?>
                    </li>
                </ul>
                <div id="navUserGreeting" class="me-3">
                    <?php if ($isUserLoggedIn): ?>
                        <span class="fw-bold text-primary">Hello, <?= htmlspecialchars($username) ?></span>
                    <?php endif; ?>
                </div>
                <?php if (!$isUserLoggedIn): ?>
                    <a href="login.html" class="btn btn-outline-primary me-2" id="loginBtn">
                        <i class="fas fa-sign-in-alt me-1"></i>Login
                    </a>
                <?php else: ?>
                    <a href="logout.php" class="btn btn-outline-danger">
                        <i class="fas fa-sign-out-alt me-1"></i>Logout
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container text-center">
            <h1 class="display-4 mb-4">Pesan Makanan Favoritmu</h1>
            <p class="lead mb-4">Nikmati berbagai pilihan makanan lezat dengan pelayanan terbaik</p>
            <form class="hero-form d-flex justify-content-center gap-2">
                <input type="email" class="form-control" style="max-width: 300px;" placeholder="Email Anda">
                <button type="submit" class="btn btn-light">Berlangganan</button>
            </form>
        </div>
    </section>

    <!-- Menu Section -->
    <section id="menu" class="py-5">
        <div class="container">
            <h2 class="text-center mb-4">Kategori</h2>
            <!-- Category Filter -->
            <div class="text-center mb-4">
                <div class="btn-group category-filter" id="category-filter" role="group">
                    <button type="button" class="btn btn-outline-primary active" data-filter="*">Semua Menu</button>
                    <button type="button" class="btn btn-outline-primary" data-filter=".makanan-berat">Makanan Berat</button>
                    <button type="button" class="btn btn-outline-primary" data-filter=".makanan-ringan">Makanan Ringan</button>
                </div>
            </div>
            <!-- Menu Container -->
            <div class="row g-4" id="menuContainer">
                <?php foreach ($products as $product): ?>
                <div class="col-md-4 menu-item <?= $product['category'] === 'MakananBerat' ? 'makanan-berat' : 'makanan-ringan' ?>">
                    <div class="card h-100 shadow-sm">
                        <img src="<?= htmlspecialchars($product['image_url']) ?>" class="card-img-top menu-image" alt="<?= htmlspecialchars($product['name']) ?>">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title"><?= htmlspecialchars($product['name']) ?></h5>
                            <p class="card-text"><?= htmlspecialchars($product['description']) ?></p>
                            <div class="mb-2">
                                <span class="badge bg-<?= $product['category'] === 'MakananBerat' ? 'primary' : 'secondary' ?>">
                                    <?= $product['category'] === 'MakananBerat' ? 'Makanan Berat' : 'Makanan Ringan' ?>
                                </span>
                            </div>
                            <div class="mb-2">
                                <span class="badge bg-info text-dark">
                                    Stok: <?= (int)$product['stock'] ?>
                                </span>
                            </div>
                            <div class="mt-auto d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-success">Rp <?= number_format($product['price'], 0, ',', '.') ?></span>
                                <button class="btn btn-outline-primary btn-sm"
                                    onclick="handleOrder(<?= $product['id'] ?>, '<?= htmlspecialchars(addslashes($product['name'])) ?>', <?= (int)$product['stock'] ?>)"
                                    <?= $product['stock'] < 1 ? 'disabled' : '' ?>>
                                    <i class="fas fa-shopping-cart me-1"></i>Pesan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach ?>
            </div>
        </div>
    </section>

    <!-- About Section -->
    <section id="about" class="py-5 bg-light">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2 class="mb-4">Tentang FoodOrder</h2>
                    <p class="lead">Kami menyediakan berbagai pilihan makanan berkualitas dengan layanan terbaik untuk kepuasan pelanggan.</p>
                    <p>Nikmati pengalaman memesan makanan yang mudah dan menyenangkan bersama FoodOrder.</p>
                </div>
                <div class="col-md-6">
                    <img src="https://images.unsplash.com/photo-1581349485608-9469926a8e5e?w=600" 
                         alt="Restaurant" class="img-fluid rounded shadow">
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5><i class="fas fa-utensils me-2"></i>FoodOrder</h5>
                    <p>Solusi terbaik untuk kebutuhan kuliner Anda</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p>&copy; <span id="currentYear"></span> FoodOrder. All rights reserved.</p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Modals -->
    <!-- Login Required Modal -->
    <div class="modal fade" id="loginRequiredModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-lock me-2"></i>Login Diperlukan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <p>Silakan login terlebih dahulu untuk melihat riwayat pesanan.</p>
                    <a href="login.html" class="btn btn-primary">
                        <i class="fas fa-sign-in-alt me-2"></i>Login
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Order Quantity Modal -->
    <div class="modal fade" id="orderQtyModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-shopping-cart me-2"></i>Jumlah Pesanan</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="orderQtyForm">
                    <div class="modal-body">
                        <h6 id="qtyMenuName" class="mb-3"></h6>
                        <div class="input-group">
                            <button class="btn btn-outline-secondary" type="button" id="btnQtyMinus">
                                <i class="fas fa-minus"></i>
                            </button>
                            <input type="number" class="form-control text-center" id="qtyInput" value="1" min="1" required>
                            <button class="btn btn-outline-secondary" type="button" id="btnQtyPlus">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Pesan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/isotope-layout@3/dist/isotope.pkgd.min.js"></script>
    <script src="https://unpkg.com/imagesloaded@5/imagesloaded.pkgd.min.js"></script>
    <script>
        // Category filter functionality
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Isotope
            var menuContainer = document.querySelector('#menuContainer');
            var iso = new Isotope(menuContainer, {
                itemSelector: '.menu-item',
                layoutMode: 'fitRows'
            });

            // Filter buttons
            var filterButtons = document.querySelectorAll('#category-filter button');
            filterButtons.forEach(function(button) {
                button.addEventListener('click', function() {
                    // Remove active class from all buttons
                    filterButtons.forEach(function(btn) {
                        btn.classList.remove('active');
                    });
                    // Add active class to clicked button
                    this.classList.add('active');
                    
                    // Filter items
                    var filterValue = this.getAttribute('data-filter');
                    iso.arrange({ filter: filterValue });
                });
            });

            // Set current year
            document.getElementById('currentYear').textContent = new Date().getFullYear();
        });

        // User session info for order
        let currentUser = <?= $isUserLoggedIn ? json_encode(['user_id' => $user_id, 'username' => $username]) : 'null' ?>;
        let currentMaxStock = 1;

        function handleOrder(productId, productName, stock) {
            if (!currentUser) {
                var loginModal = new bootstrap.Modal(document.getElementById('loginRequiredModal'));
                loginModal.show();
                return;
            }
            document.getElementById('qtyMenuName').textContent = productName;
            document.getElementById('qtyInput').value = 1;
            document.getElementById('qtyInput').max = stock;
            currentMaxStock = stock;
            document.getElementById('orderQtyForm').setAttribute('data-product-id', productId);
            var qtyModal = new bootstrap.Modal(document.getElementById('orderQtyModal'));
            qtyModal.show();
        }

        // Batasi input jumlah pesanan sesuai stok
        document.getElementById('btnQtyPlus').onclick = function() {
            let qtyInput = document.getElementById('qtyInput');
            let val = parseInt(qtyInput.value) || 1;
            if (val < currentMaxStock) qtyInput.value = val + 1;
        };
        document.getElementById('btnQtyMinus').onclick = function() {
            let qtyInput = document.getElementById('qtyInput');
            let val = parseInt(qtyInput.value) || 1;
            if (val > 1) qtyInput.value = val - 1;
        };
        document.getElementById('qtyInput').addEventListener('input', function() {
            let val = parseInt(this.value) || 1;
            if (val < 1) this.value = 1;
            if (val > currentMaxStock) this.value = currentMaxStock;
        });

        document.getElementById('orderQtyForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            if (!currentUser) {
                var loginModal = new bootstrap.Modal(document.getElementById('loginRequiredModal'));
                loginModal.show();
                return;
            }
            const productId = this.getAttribute('data-product-id');
            const quantity = document.getElementById('qtyInput').value;
            try {
                const response = await fetch('order_process.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        user_id: currentUser.user_id,
                        product_id: productId,
                        quantity: quantity
                    })
                });
                const result = await response.json();
                if (result.success) {
                    alert('Pesanan berhasil!');
                    bootstrap.Modal.getInstance(document.getElementById('orderQtyModal')).hide();
                    location.reload();
                } else {
                    alert(result.message || 'Gagal memproses pesanan');
                }
            } catch (error) {
                alert('Terjadi kesalahan server');
            }
        });

        function showLoginRequiredModal(event) {
            event.preventDefault();
            var loginModal = new bootstrap.Modal(document.getElementById('loginRequiredModal'));
            loginModal.show();
        }
    </script>
</body>
</html>