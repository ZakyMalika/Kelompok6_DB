<?php
session_start();

header('Content-Type: application/json');

$host = 'localhost';
$dbname = 'restorant';
$user = 'root';
$pass = '';

try {
    $conn = new mysqli($host, $user, $pass, $dbname);
    if ($conn->connect_error) {
        throw new Exception("Koneksi gagal: " . $conn->connect_error);
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = isset($_POST['name']) ? trim($_POST['name']) : '';
        $password = isset($_POST['password']) ? $_POST['password'] : '';
        $type = isset($_POST['type']) ? $_POST['type'] : '';

        if (empty($name) || empty($password) || empty($type)) {
            throw new Exception("Semua field harus diisi!");
        }

        $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE name = ?");
        $stmt->bind_param("s", $name);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $userData = $result->fetch_assoc();
            if (password_verify($password, $userData['password'])) {
                if ($userData['role'] !== $type) {
                    throw new Exception("Role tidak sesuai!");
                }
                $_SESSION['logged_in'] = true;
                $_SESSION['user_id'] = $userData['id'];
                $_SESSION['username'] = $userData['name'];
                $_SESSION['user_type'] = $userData['role'];

                if ($userData['role'] === 'admin') {
                    echo json_encode(['success' => true, 'message' => 'Login admin berhasil!', 'redirect' => 'admin_dashboard.php']);
                } else {
                    echo json_encode(['success' => true, 'message' => 'Login user berhasil!', 'redirect' => 'index.php']);
                }
                exit;
            } else {
                throw new Exception("Password salah!");
            }
        } else {
            throw new Exception("Username tidak ditemukan!");
        }
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
$conn->close();
?>
