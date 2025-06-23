<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola User - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/dataTables.bootstrap5.min.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-none d-md-block bg-dark sidebar py-4">
                <div class="sidebar-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item mb-2">
                            <a class="nav-link text-white" href="admin_dashboard.php">
                                <i class="fas fa-home me-2"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav-item mb-2">
                            <a class="nav-link text-white active" href="admin_users.php">
                                <i class="fas fa-users me-2"></i>Kelola Users
                            </a>
                        </li>
                        <!-- Add other menu items here -->
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-10 ms-sm-auto px-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                    <h1>Kelola Users</h1>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="fas fa-plus me-2"></i>Tambah User
                    </button>
                </div>

                <!-- Users Table -->
                <div class="table-responsive">
                    <table id="usersTable" class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            include 'config.php';
                            $sql = "SELECT * FROM users ORDER BY id DESC";
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
                                        <select class="form-select" name="role" required>
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
                                        <input type="password" class="form-control" name="password" placeholder="Leave blank to keep current password">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label">Role</label>
                                        <select class="form-select" name="role" id="edit_role" required>
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
            </main>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#usersTable').DataTable();
        });

        // Add User
        document.getElementById('addUserForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            try {
                const response = await fetch('admin_add_user.php', {
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

        // Edit User
        async function editUser(id) {
            try {
                const response = await fetch(`admin_get_user.php?id=${id}`);
                const user = await response.json();
                
                document.getElementById('edit_user_id').value = user.id;
                document.getElementById('edit_username').value = user.name;
                document.getElementById('edit_role').value = user.role;
                
                new bootstrap.Modal(document.getElementById('editUserModal')).show();
            } catch (error) {
                alert('Terjadi kesalahan!');
            }
        }

        // Update User
        document.getElementById('editUserForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            
            try {
                const response = await fetch('admin_update_user.php', {
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

        // Delete User
        async function deleteUser(id) {
            if (!confirm('Apakah Anda yakin ingin menghapus user ini?')) return;
            
            try {
                const response = await fetch('admin_delete_user.php', {
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
    </script>
</body>
</html>
