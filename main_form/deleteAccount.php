<?php
session_start();

// Jika tidak ada sesi username, arahkan ke login
if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

include('../db_connect/DatabaseConnection.php');

$error = '';
$success = '';
$current_username = $_SESSION['username'];

// Cek apakah username ada di tabel `users` atau `publisher`
$user_query = "SELECT * FROM users WHERE username = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("s", $current_username);
$user_stmt->execute();
$user_result = $user_stmt->get_result();

if ($user_result->num_rows > 0) {
    // Jika username ditemukan di tabel `users`
    $table = "users";
    $column = "username";
} else {
    // Periksa di tabel `publisher`
    $publisher_query = "SELECT * FROM publisher WHERE publisher_name = ?";
    $publisher_stmt = $conn->prepare($publisher_query);
    $publisher_stmt->bind_param("s", $current_username);
    $publisher_stmt->execute();
    $publisher_result = $publisher_stmt->get_result();

    if ($publisher_result->num_rows > 0) {
        // Jika username ditemukan di tabel `publisher`
        $table = "publisher";
        $column = "publisher_name";
    } else {
        // Jika username tidak ditemukan di kedua tabel, logout
        session_destroy();
        header("Location: ../auth/login.php");
        exit();
    }
}

// Jika metode POST digunakan untuk menghapus akun
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $delete_query = "DELETE FROM $table WHERE $column = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param("s", $current_username);

    if ($delete_stmt->execute()) {
        // Hapus sesi dan arahkan ke halaman login
        session_destroy();
        header("Location: ../auth/login.php");
        exit();
    } else {
        $error = "Failed to delete account.";
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Delete Account</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #2C2C2C; color: white; font-family: Arial, sans-serif; }
        .container { display: flex; justify-content: center; align-items: center; height: 100vh; }
        .delete-account-box { background-color: rgba(0, 0, 0, 0.8); padding: 30px 25px; border-radius: 10px; width: 100%; max-width: 400px; }
        .btn-danger { background-color: #dc3545; border: none; width: 100%; }
        .btn-danger:hover { background-color: #b02a37; }
    </style>
</head>
<body>
    <div class="container">
        <div class="delete-account-box">
            <h2>Delete Account</h2>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <form method="POST">
                <p>Are you sure you want to delete your account? This action is permanent.</p>
                <button type="submit" class="btn btn-danger">Delete Account</button>
            </form>
        </div>
    </div>
</body>
</html>