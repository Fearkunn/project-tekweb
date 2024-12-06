<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

include('../db_connect/DatabaseConnection.php');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_SESSION['username'];
    $query = "DELETE FROM users WHERE username = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    if ($stmt->execute()) {
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
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            <form method="POST">
                <p>Are you sure you want to delete your account? This action is permanent.</p>
                <button type="submit" class="btn btn-danger">Delete Account</button>
            </form>
        </div>
    </div>
</body>
</html>