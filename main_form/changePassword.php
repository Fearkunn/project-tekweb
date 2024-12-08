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

// Periksa apakah username ada di tabel `users` atau `publisher`
$user_query = "SELECT user_password FROM users WHERE username = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("s", $current_username);
$user_stmt->execute();
$user_result = $user_stmt->get_result();

if ($user_result->num_rows > 0) {
    // Jika username ditemukan di tabel `users`
    $table = "users";
    $password_column = "user_password";
    $update_query = "UPDATE users SET user_password = ? WHERE username = ?";
} else {
    // Periksa di tabel `publisher`
    $publisher_query = "SELECT publisher_password FROM publisher WHERE publisher_name = ?";
    $publisher_stmt = $conn->prepare($publisher_query);
    $publisher_stmt->bind_param("s", $current_username);
    $publisher_stmt->execute();
    $publisher_result = $publisher_stmt->get_result();

    if ($publisher_result->num_rows > 0) {
        // Jika username ditemukan di tabel `publisher`
        $table = "publisher";
        $password_column = "publisher_password";
        $update_query = "UPDATE publisher SET publisher_password = ? WHERE publisher_name = ?";
    } else {
        // Jika username tidak ditemukan di kedua tabel, logout
        session_destroy();
        header("Location: ../auth/login.php");
        exit();
    }
}

// Jika metode POST digunakan untuk mengubah password
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "All fields are required!";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match!";
    } else {
        // Ambil password saat ini dari database
        $stmt = $conn->prepare("SELECT $password_column FROM $table WHERE " . ($table === "users" ? "username = ?" : "publisher_name = ?"));
        $stmt->bind_param("s", $current_username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($current_password, $user[$password_column])) {
                // Hash password baru
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                // Update password
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bind_param("ss", $hashed_password, $current_username);
                if ($update_stmt->execute()) {
                    $_SESSION['Send'] = ['type' => 'success', 'message' => 'Change Password Success', 'redirect' => 'mainForm.php'];
                } else {
                    $_SESSION['Send'] = ['type' => 'error', 'message' => 'Failed to update password.'];
                    header("Location: ../main_form/changePassword.php");
                    exit();
                }
            } else {
                $_SESSION['Send'] = ['type' => 'error', 'message' => 'Incorrect current password!'];
                header("Location: ../main_form/changePassword.php");
                exit();
            }
        } else {
            $_SESSION['Send'] = ['type' => 'error', 'message' => 'User not found!'];
            header("Location: ../main_form/changePassword.php");
            exit();
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Change Password</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="icon" href="../assets/UAP.ico" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #2C2C2C; 
        }
        #password-section{
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-image: url('../assets/Background.png');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center top;
            margin: 0;
            padding: 0;
        }
        .navbar {
            background-color: #2C2C2C;
            font-family: Arial, sans-serif;
            padding: 10px 20px;
        }
        .navbar-brand, .nav-link {
            color: #FFFFFF !important;
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
        .form-box { 
            background-color: rgba(0, 0, 0, 0.8);
            padding: 30px 25px;
            border-radius: 10px;
            color: white;
            width: 100%;
            max-width: 400px;
            margin: 10px 0; 
        }
        .container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .btn-primary { background-color: #007bff; border: none; width: 100%; }
        .btn-primary:hover { background-color: #0056b3; }
        .btn-secondary { background-color: #6c757d; border: none; width: 100%; margin-top: 5px; margin-bottom: 10px; }
        .btn-secondary:hover { background-color: #5a6268; }
    </style>
</head>
<body>
    <?php if (isset($_SESSION['Send'])): ?>
                <script>
                    Swal.fire({
                        title: "<?= $_SESSION['Send']['type'] === 'success' ? 'Berhasil!' : 'Gagal!' ?>",
                        text: "<?= $_SESSION['Send']['message'] ?>",
                        icon: "<?= $_SESSION['Send']['type'] ?>",
                        confirmButtonText: "OK"
                    }).then(() => {
                        <?php if ($_SESSION['Send']['type'] === 'success' && isset($_SESSION['Send']['redirect'])): ?>
                            window.location.href = "<?= $_SESSION['Send']['redirect'] ?>";
                        <?php endif; ?>
                    });
                </script>
            <?php unset($_SESSION['Send']); ?>
        <?php endif; ?>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand mx-auto" href="../main_form/mainForm.php">
                <img src="../assets/UapLogoText.svg" alt="UapLogo">
            </a>
        </div>
    </nav>

    <section id="password-section">
        <div class="container">
            <div class="form-box">
                <h2 class="pb-3">Change Password</h2>
                <form method="POST">
                    <div class="mb-3">
                        <label for="current_password" class="form-label">Current Password</label>
                        <input type="password" class="form-control" name="current_password" id="current_password" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">New Password</label>
                        <input type="password" class="form-control" name="new_password" id="new_password" required>
                    </div>
                    <div class="mb-3 pb-2">
                        <label for="confirm_password" class="form-label">Confirm New Password</label>
                        <input type="password" class="form-control" name="confirm_password" id="confirm_password" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Password</button>
                </form>
            </div>
        </div>
    </section>
</body>
</html>