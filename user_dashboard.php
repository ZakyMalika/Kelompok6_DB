<?php
session_start();
if (!isset($_SESSION['logged_in']) || $_SESSION['user_type'] !== 'user') {
    header('Location: login.html');
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>User Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <h2>Selamat datang, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
        <p>Ini adalah halaman dashboard user.</p>
        <a href="logout.php" class="btn btn-danger">Logout</a>
    </div>
</body>
</html>
