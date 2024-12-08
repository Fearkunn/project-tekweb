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
$user_query = "SELECT * FROM users WHERE username = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("s", $current_username);
$user_stmt->execute();
$user_result = $user_stmt->get_result();

if ($user_result->num_rows > 0) {
    // Jika username ditemukan di tabel `users`
    $table = "users";
    $update_query = "UPDATE users SET username = ? WHERE username = ?";
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
        $update_query = "UPDATE publisher SET publisher_name = ? WHERE publisher_name = ?";
    } else {
        // Jika username tidak ditemukan di kedua tabel, logout
        session_destroy();
        header("Location: ../auth/login.php");
        exit();
    }
}

// Jika metode POST digunakan untuk mengubah username
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = $_POST['new_username'];

    if (empty($new_username)) {
        $_SESSION['Send'] = ['type' => 'error', 'message' => 'Username cannot be empty!'];
        header("Location: ../main_form/changeUsername.php");
        exit();
    } else {
        // Cek apakah username baru sudah digunakan di tabel `users`
        $check_user_query = "SELECT * FROM users WHERE username = ?";
        $check_user_stmt = $conn->prepare($check_user_query);
        $check_user_stmt->bind_param("s", $new_username);
        $check_user_stmt->execute();
        $user_exists = $check_user_stmt->get_result()->num_rows > 0;

        // Cek apakah username baru sudah digunakan di tabel `publisher`
        $check_publisher_query = "SELECT * FROM publisher WHERE publisher_name = ?";
        $check_publisher_stmt = $conn->prepare($check_publisher_query);
        $check_publisher_stmt->bind_param("s", $new_username);
        $check_publisher_stmt->execute();
        $publisher_exists = $check_publisher_stmt->get_result()->num_rows > 0;

        if ($user_exists || $publisher_exists) {
            $_SESSION['Send'] = ['type' => 'error', 'message' => 'Username already taken!'];
            header("Location: ../main_form/changeUsername.php");
            exit();
        } else {
            // Lakukan update username
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("ss", $new_username, $current_username);

            if ($update_stmt->execute()) {
                // Perbarui sesi dengan username baru
                $_SESSION['username'] = $new_username;
                $_SESSION['Send'] = ['type' => 'success', 'message' => 'Username berhasil diperbarui!','redirect' => '../main_form/mainForm.php'];
                
            } else {
                $_SESSION['Send'] = ['type' => 'error', 'message' => 'Terjadi kesalahan saat memperbarui username.'];
                header("Location: ../main_form/changeusername.php");
                exit();
            }
        }
    }

}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Change Username</title>
    <link rel="icon" href="../assets/UAP.ico" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { 
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #2C2C2C; 
        }
        #username-section{
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
                }).then((result) => { //jika berhasil redirect kesini
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

    <section id="username-section">
        <div class="container">
            <div class="form-box">
                <h2 class="pb-3">Change Username</h2>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3 pb-2">
                        <label for="new_username" class="form-label">New Username</label>
                        <input type="text" class="form-control" name="new_username" id="new_username" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Username</button>
                </form>
            </div>
        </div>
    </section>
</body>
</html>