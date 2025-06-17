<?php
session_start();
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
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $name = trim($_POST['name']);
        $password = $_POST['password'];
        $type = $_POST['type']; // 'user' or 'admin'
        
        // Validasi input
        if (empty($name) || empty($password)) {
            echo json_encode([
                'success' => false,
                'message' => 'Nama dan password harus diisi!'
            ]);
            exit;
        }
        
        if ($type === 'user') {
            // Login sebagai user
            $stmt = $conn->prepare("SELECT id, name, password FROM users WHERE name = ?");
            $stmt->bind_param("s", $name);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $user = $result->fetch_assoc();
                
                // Verifikasi password (jika menggunakan hash)
                if (password_verify($password, $user['password']) || $password === $user['password']) {
                    // Set session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_type'] = 'user';
                    $_SESSION['logged_in'] = true;
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Login berhasil! Mengalihkan ke halaman utama...'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Password salah!'
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Pengguna tidak ditemukan!'
                ]);
            }
            $stmt->close();
            
        } elseif ($type === 'admin') {
            // Login sebagai admin
            $stmt = $conn->prepare("SELECT id, name, password FROM admin WHERE name = ?");
            $stmt->bind_param("s", $name);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $admin = $result->fetch_assoc();
                
                // Verifikasi password (jika menggunakan hash)
                if (password_verify($password, $admin['password']) || $password === $admin['password']) {
                    // Set session
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_name'] = $admin['name'];
                    $_SESSION['user_type'] = 'admin';
                    $_SESSION['logged_in'] = true;
                    
                    echo json_encode([
                        'success' => true,
                        'message' => 'Login admin berhasil! Mengalihkan ke dashboard...'
                    ]);
                } else {
                    echo json_encode([
                        'success' => false,
                        'message' => 'Password admin salah!'
                    ]);
                }
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Admin tidak ditemukan!'
                ]);
            }
            $stmt->close();
            
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Tipe login tidak valid!'
            ]);
        }
        
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Method tidak diizinkan!'
        ]);
    }
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

$conn->close();
?>