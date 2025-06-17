<?php
session_start();
header('Content-Type: application/json');

// Cek apakah user sudah login
if (isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    echo json_encode([
        'logged_in' => true,
        'user_type' => $_SESSION['user_type'],
        'user_name' => isset($_SESSION['user_name']) ? $_SESSION['user_name'] : $_SESSION['admin_name']
    ]);
} else {
    echo json_encode([
        'logged_in' => false
    ]);
}
?>