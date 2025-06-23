<?php
session_start();
header('Content-Type: application/json');
echo json_encode([
    'logged_in' => isset($_SESSION['logged_in']) ? $_SESSION['logged_in'] : false,
    'username' => isset($_SESSION['username']) ? $_SESSION['username'] : null,
    'user_type' => isset($_SESSION['user_type']) ? $_SESSION['user_type'] : null
]);
?>
