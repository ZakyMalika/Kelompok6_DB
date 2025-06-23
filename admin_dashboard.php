<?php
session_start();

// Cek apakah user sudah login sebagai admin
if (!isset($_SESSION['logged_in']) || $_SESSION['user_type'] !== 'admin') {
    header('Location: login.html');
    exit;
}

// Database connection
$host = 'localhost';
$dbname = 'restorant';
$user = 'root';
$pass = '';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle CRUD operations for products
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_action'])) {
    $action = $_POST['product_action'];
    if ($action === 'create') {
        $name = $_POST['product_name'];
        $description = $_POST['product_description'];
        $price = $_POST['product_price'];
        $image_url = $_POST['product_image_url'];
        $category = $_POST['product_category'];
        $stock = $_POST['product_stock'];
        $stmt = $conn->prepare("INSERT INTO produck (name, description, price, image_url, category, stock) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssdssi", $name, $description, $price, $image_url, $category, $stock);
        $stmt->execute();
        $stmt->close();
    } elseif ($action === 'update') {
        $id = $_POST['product_id'];
        $name = $_POST['product_name'];
        $description = $_POST['product_description'];
        $price = $_POST['product_price'];
        $image_url = $_POST['product_image_url'];
        $category = $_POST['product_category'];
        $stock = $_POST['product_stock'];
        $stmt = $conn->prepare("UPDATE produck SET name=?, description=?, price=?, image_url=?, category=?, stock=? WHERE id=?");
        $stmt->bind_param("ssdssii", $name, $description, $price, $image_url, $category, $stock, $id);
        $stmt->execute();
        $stmt->close();
    } elseif ($action === 'delete') {
        $id = $_POST['product_id'];
        $stmt = $conn->prepare("DELETE FROM produck WHERE id=?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->close();
    }
}

// Fetch all products
$result = $conn->query("SELECT * FROM produck ORDER BY id DESC");
$products = [];
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $products[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - FoodOrder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
    <style>
        .sidebar {
            min-height: 100vh;
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
        }
        .sidebar .nav-link {
            color: #333;
            padding: 0.5rem 1rem;
        }
        .sidebar .nav-link.active {
            color: #2470dc;
            background-color: #e9ecef;
        }
        .main-content {
            padding: 20px;
        }
        .card {
            margin-bottom: 20px;
        }
        .table-container {
            border-radius: 15px;
            overflow: hidden;
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
            <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
                <div class="position-sticky pt-3">
                    <ul class="nav flex-column">
                        <li class="nav-item mb-2">
                            <a class="nav-link active" href="admin_dashboard.php">
                                <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a class="nav-link" href="#userManagement" data-bs-toggle="collapse">
                                <i class="fas fa-users me-2"></i>Kelola User
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a class="nav-link" href="#productManagement" data-bs-toggle="collapse">
                                <i class="fas fa-box me-2"></i>Kelola Produk
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a class="nav-link" href="admin_orders.php">
                                <i class="fas fa-history me-2"></i>Riwayat Pesanan
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a class="nav-link" href="logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i>Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1>Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                            <i class="fas fa-user-plus me-2"></i>Tambah User
                        </button>
                    </div>
                </div>

                <!-- User Management Section -->
                <div class="card" id="userManagement">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Daftar User</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="userTable" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Role</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    include 'config.php';
                                    $sql = "SELECT id, name, role FROM users ORDER BY id DESC";
                                    $result = $conn->query($sql);

                                    while($row = $result->fetch_assoc()) {
                                        echo "<tr>
                                            <td>{$row['id']}</td>
                                            <td>{$row['name']}</td>
                                            <td>{$row['role']}</td>
                                            <td>
                                                <button class='btn btn-sm btn-primary' onclick='editUser({$row['id']})'>
                                                    <i class='fas fa-edit'></i>
                                                </button>
                                                <button class='btn btn-sm btn-danger' onclick='deleteUser({$row['id']})'>
                                                    <i class='fas fa-trash'></i>
                                                </button>
                                            </td>
                                        </tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Product Management Section -->
                <div class="card mt-4" id="productManagement">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Daftar Produk</h5>
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addProductModal">
                            <i class="fas fa-plus"></i> Tambah Produk
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="productTable" class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Gambar</th>
                                        <th>Nama</th>
                                        <th>Kategori</th>
                                        <th>Harga</th>
                                        <th>Stok</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($products as $product): ?>
                                    <tr>
                                        <td><?= $product['id'] ?></td>
                                        <td>
                                            <img src="<?= htmlspecialchars($product['image_url']) ?>" class="product-image" alt="<?= htmlspecialchars($product['name']) ?>">
                                        </td>
                                        <td><?= htmlspecialchars($product['name']) ?></td>
                                        <td><?= htmlspecialchars($product['category']) ?></td>
                                        <td>Rp <?= number_format($product['price'], 0, ',', '.') ?></td>
                                        <td><?= $product['stock'] ?></td>
                                        <td>
                                            <button class="btn btn-warning btn-sm" onclick='editProduct(<?= json_encode($product) ?>)'><i class="fas fa-edit"></i></button>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Yakin hapus produk ini?')">
                                                <input type="hidden" name="product_action" value="delete">
                                                <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                                                <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endforeach ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Add User Modal -->
    <div class="modal fade" id="addUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="addUserForm">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select class="form-select" name="role">
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Edit User</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editUserForm">
                    <input type="hidden" name="user_id" id="edit_user_id">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Username</label>
                            <input type="text" class="form-control" name="username" id="edit_username" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" class="form-control" name="password" placeholder="Kosongkan jika tidak ingin mengubah password">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Role</label>
                            <select class="form-select" name="role" id="edit_role">
                                <option value="user">User</option>
                                <option value="admin">Admin</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Tambah Produk -->
    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog">
            <form class="modal-content" method="POST">
                <input type="hidden" name="product_action" value="create">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Produk</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Produk</label>
                        <input type="text" class="form-control" name="product_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="product_description" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Harga</label>
                        <input type="number" class="form-control" name="product_price" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">URL Gambar</label>
                        <input type="text" class="form-control" name="product_image_url" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <select class="form-select" name="product_category" required>
                            <option value="MakananBerat">Makanan Berat</option>
                            <option value="MakananRingan">Makanan Ringan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stok</label>
                        <input type="number" class="form-control" name="product_stock" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Edit Produk -->
    <div class="modal fade" id="editProductModal" tabindex="-1">
        <div class="modal-dialog">
            <form class="modal-content" method="POST" id="editProductForm">
                <input type="hidden" name="product_action" value="update">
                <input type="hidden" name="product_id" id="edit_product_id">
                <div class="modal-header">
                    <h5 class="modal-title">Edit Produk</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama Produk</label>
                        <input type="text" class="form-control" name="product_name" id="edit_product_name" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" name="product_description" id="edit_product_description" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Harga</label>
                        <input type="number" class="form-control" name="product_price" id="edit_product_price" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">URL Gambar</label>
                        <input type="text" class="form-control" name="product_image_url" id="edit_product_image_url" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <select class="form-select" name="product_category" id="edit_product_category" required>
                            <option value="MakananBerat">Makanan Berat</option>
                            <option value="MakananRingan">Makanan Ringan</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Stok</label>
                        <input type="number" class="form-control" name="product_stock" id="edit_product_stock" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning">Update</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#userTable').DataTable();
            $('#productTable').DataTable();
        });

        // Add User Form Submit
        document.getElementById('addUserForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            try {
                const response = await fetch('process_register.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                if (result.success) {
                    alert('User berhasil ditambahkan!');
                    location.reload();
                } else {
                    alert(result.message);
                }
            } catch (error) {
                alert('Terjadi kesalahan!');
            }
        });

        // Edit User Function
        function editUser(id) {
            // Fetch user data and populate the form
            // Show the modal
            var myModal = new bootstrap.Modal(document.getElementById('editUserModal'));
            myModal.show();
        }

        // Update User Form Submit
        document.getElementById('editUserForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            try {
                const response = await fetch('update_user.php', {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                if (result.success) {
                    alert('User berhasil diupdate!');
                    location.reload();
                } else {
                    alert(result.message);
                }
            } catch (error) {
                alert('Terjadi kesalahan!');
            }
        });

        // Delete User Function
        async function deleteUser(id) {
            if (!confirm('Apakah Anda yakin ingin menghapus user ini?')) return;
            
            try {
                const response = await fetch('delete_user.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ id })
                });
                
                const result = await response.json();
                if (result.success) {
                    alert('User berhasil dihapus!');
                    location.reload();
                } else {
                    alert(result.message);
                }
            } catch (error) {
                alert('Terjadi kesalahan!');
            }
        }

        // Edit Product Modal
        function editProduct(product) {
            document.getElementById('edit_product_id').value = product.id;
            document.getElementById('edit_product_name').value = product.name;
            document.getElementById('edit_product_description').value = product.description;
            document.getElementById('edit_product_price').value = product.price;
            document.getElementById('edit_product_image_url').value = product.image_url;
            document.getElementById('edit_product_category').value = product.category;
            document.getElementById('edit_product_stock').value = product.stock;
            var myModal = new bootstrap.Modal(document.getElementById('editProductModal'));
            myModal.show();
        }
    </script>
</body>
</html>