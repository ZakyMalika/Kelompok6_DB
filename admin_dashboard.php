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

// Handle CRUD operations
$message = '';
$alert_type = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    
    switch ($action) {
        case 'create':
            $name = $_POST['name'];
            $description = $_POST['description'];
            $price = $_POST['price'];
            $image_url = $_POST['image_url'];
            $category = $_POST['category'];
            $stock = $_POST['stock'];
            
            $stmt = $conn->prepare("INSERT INTO produck (name, description, price, image_url, category, stock) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssdssi", $name, $description, $price, $image_url, $category, $stock);
            
            if ($stmt->execute()) {
                $message = "Produk berhasil ditambahkan!";
                $alert_type = "success";
            } else {
                $message = "Error: " . $stmt->error;
                $alert_type = "danger";
            }
            $stmt->close();
            break;
            
        case 'update':
            $id = $_POST['id'];
            $name = $_POST['name'];
            $description = $_POST['description'];
            $price = $_POST['price'];
            $image_url = $_POST['image_url'];
            $category = $_POST['category'];
            $stock = $_POST['stock'];
            
            $stmt = $conn->prepare("UPDATE produck SET name=?, description=?, price=?, image_url=?, category=?, stock=? WHERE id=?");
            $stmt->bind_param("ssdssii", $name, $description, $price, $image_url, $category, $stock, $id);
            
            if ($stmt->execute()) {
                $message = "Produk berhasil diupdate!";
                $alert_type = "success";
            } else {
                $message = "Error: " . $stmt->error;
                $alert_type = "danger";
            }
            $stmt->close();
            break;
            
        case 'delete':
            $id = $_POST['id'];
            
            $stmt = $conn->prepare("DELETE FROM produck WHERE id=?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $message = "Produk berhasil dihapus!";
                $alert_type = "success";
            } else {
                $message = "Error: " . $stmt->error;
                $alert_type = "danger";
            }
            $stmt->close();
            break;
    }
}

// Fetch all products
$result = $conn->query("SELECT * FROM produck ORDER BY id DESC");
$products = [];
if ($result->num_rows > 0) {
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
        .btn-primary {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            border-radius: 25px;
        }
        .btn-primary:hover {
            background: linear-gradient(45deg, #5a6fd8, #6a4190);
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
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <div class="p-4">
                    <h4 class="text-center mb-4"><i class="fas fa-utensils me-2"></i>FoodOrder</h4>
                    <div class="text-center mb-4">
                        <h6>Selamat Datang</h6>
                        <p class="mb-0"><?php echo $_SESSION['admin_name']; ?></p>
                    </div>
                    <nav class="nav flex-column">
                        <a class="nav-link active" href="#"><i class="fas fa-box me-2"></i>Kelola Produk</a>
                        <a class="nav-link" href="admin_orders.php"><i class="fas fa-history me-2"></i>Riwayat Pesanan</a>
                        <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
                    </nav>
                </div>
            </div>
            
            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Kelola Produk</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addProductModal">
                        <i class="fas fa-plus me-2"></i>Tambah Produk
                    </button>
                </div>
                
                <?php if ($message): ?>
                <div class="alert alert-<?php echo $alert_type; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
                <?php endif; ?>
                
                <!-- Products Table -->
                <div class="card table-container">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-dark">
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
                                        <td><?php echo $product['id']; ?></td>
                                        <td>
                                            <img src="<?php echo $product['image_url']; ?>" 
                                                 alt="<?php echo $product['name']; ?>" 
                                                 class="product-image">
                                        </td>
                                        <td><?php echo $product['name']; ?></td>
                                        <td>
                                            <span class="badge bg-<?php echo $product['category'] === 'MakananBerat' ? 'primary' : 'secondary'; ?>">
                                                <?php echo $product['category']; ?>
                                            </span>
                                        </td>
                                        <td>Rp <?php echo number_format($product['price'], 0, ',', '.'); ?></td>
                                        <td><?php echo $product['stock']; ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-warning me-1" 
                                                    onclick="editProduct(<?php echo htmlspecialchars(json_encode($product)); ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-danger" 
                                                    onclick="deleteProduct(<?php echo $product['id']; ?>, '<?php echo $product['name']; ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
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
    
    <!-- Add Product Modal -->
    <div class="modal fade" id="addProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" id="addProductForm" enctype="multipart/form-data">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title"><i class="fas fa-plus me-2"></i>Tambah Produk</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="create">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nama Produk</label>
                                    <input type="text" class="form-control" name="name" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Harga</label>
                                    <input type="number" class="form-control" name="price" step="0.01" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Kategori</label>
                                    <select class="form-control" name="category" required>
                                        <option value="MakananBerat">Makanan Berat</option>
                                        <option value="MakananRingan">Makanan Ringan</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">URL Gambar</label>
                                    <input type="url" class="form-control" name="image_url" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Stok</label>
                                    <input type="number" class="form-control" name="stock" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="description" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>

                <!-- Tambahkan script ini di bagian bawah sebelum closing body tag -->
                <script>
                document.getElementById('addProductForm').addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    try {
                        const formData = new FormData(this);
                        const response = await fetch('add_product.php', {
                            method: 'POST',
                            body: formData
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            alert('Produk berhasil ditambahkan!');
                            window.location.reload();
                        } else {
                            alert('Error: ' + result.message);
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Terjadi kesalahan saat menambahkan produk');
                    }
                });
                </script>
            </div>
        </div>
    </div>
    
    <!-- Edit Product Modal -->
    <div class="modal fade" id="editProductModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" id="editForm">
                    <div class="modal-header bg-warning text-dark">
                        <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Edit Produk</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="update">
                        <input type="hidden" name="id" id="editId">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">Nama Produk</label>
                                    <input type="text" class="form-control" name="name" id="editName" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Harga</label>
                                    <input type="number" class="form-control" name="price" id="editPrice" step="0.01" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Kategori</label>
                                    <select class="form-control" name="category" id="editCategory" required>
                                        <option value="MakananBerat">Makanan Berat</option>
                                        <option value="MakananRingan">Makanan Ringan</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label">URL Gambar</label>
                                    <input type="url" class="form-control" name="image_url" id="editImageUrl" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Stok</label>
                                    <input type="number" class="form-control" name="stock" id="editStock" required>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Deskripsi</label>
                            <textarea class="form-control" name="description" id="editDescription" rows="3" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function editProduct(product) {
        document.getElementById('editId').value = product.id;
        document.getElementById('editName').value = product.name;
        document.getElementById('editPrice').value = product.price;
        document.getElementById('editCategory').value = product.category;
        document.getElementById('editImageUrl').value = product.image_url;
        document.getElementById('editStock').value = product.stock;
        document.getElementById('editDescription').value = product.description;
        
        var myModal = new bootstrap.Modal(document.getElementById('editProductModal'));
        myModal.show();
    }

    function deleteProduct(id, name) {
        if (confirm("Anda yakin ingin menghapus produk '" + name + "'?")) {
            var form = document.createElement('form');
            form.method = 'POST';
            form.action = '';

            var hiddenField1 = document.createElement('input');
            hiddenField1.type = 'hidden';
            hiddenField1.name = 'action';
            hiddenField1.value = 'delete';
            form.appendChild(hiddenField1);

            var hiddenField2 = document.createElement('input');
            hiddenField2.type = 'hidden';
            hiddenField2.name = 'id';
            hiddenField2.value = id;
            form.appendChild(hiddenField2);

            document.body.appendChild(form);
            form.submit();
        }
    }
    </script>
</body>
</html>