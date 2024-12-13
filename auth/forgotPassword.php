<?php
session_start();

// Koneksi ke database
include('../db_connect/DatabaseConnection.php');

// Jika form dikirimkan
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['user_email'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    // Validasi apakah password baru cocok dengan konfirmasi
    if ($new_password !== $confirm_password) {
        $_SESSION['reset_status'] = 'password_mismatch';
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        // Query untuk memeriksa apakah email ada
        $sql = "SELECT * FROM users WHERE user_email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            // Jika email ditemukan, update password
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_sql = "UPDATE users SET user_password = ? WHERE user_email = ?";
            $update_stmt = $conn->prepare($update_sql);
            $update_stmt->bind_param("ss", $hashed_password, $email);

            if ($update_stmt->execute()) {
                $_SESSION['reset_status'] = 'success';
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                $_SESSION['reset_status'] = 'failed';
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
            $update_stmt->close();
        } else {
            // Jika email tidak ditemukan
            $_SESSION['reset_status'] = 'email_not_found';
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
        $stmt->close();
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password</title>
    <link rel="icon" href="../assets/UAP.ico" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.1/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #2C2C2C;
            color: white;
        }

        #forgot-password-section {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-image: url('../assets/Background.png');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center top;
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

        .container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .forgot-password-box {
            background-color: rgba(0, 0, 0, 0.8);
            padding: 30px 25px;
            border-radius: 10px;
            color: white;
            width: 100%;
            max-width: 400px;
        }
        
        section .btn-primary {
            padding: 10px 20px;
            font-size: 1rem;
            border-radius: 5px;
        }

        footer {
            font-size: 0.9rem;
            color: #AAA;
            text-align: center;
            padding: 30px;
            background-color: #1C1C1C;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand mx-auto" href="..\main_form\mainForm.php">
                <img src="..\assets\UapLogoText.svg" alt="UapLogo">
            </a>
        </div>
    </nav>
    
    <!-- Section Forgot Password -->
    <section id="forgot-password-section">
        <div class="container">
            <div class="forgot-password-box">
                <h2 class="pb-3">Lupa Password</h2>
                <form action="#" method="POST">
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="user_email" placeholder="Masukkan email Anda" required>
                    </div>
                    <div class="mb-3">
                        <label for="new_password" class="form-label">Password Baru</label>
                        <input type="password" class="form-control" id="new_password" name="new_password" placeholder="Masukkan password baru" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password Baru</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Konfirmasi password baru" required>
                    </div>
                    <div class="pt-4">
                        <button type="submit" class="btn btn-primary w-100">Reset Password</button>
                    </div>
                </form>
                <div class="text-center mt-4">
                    <a href="..\auth\Login.php" class="text-decoration-none text-info">Ingat password Anda? Login</a>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="text-center text-white">
        <div class="container">
            <p>© 2024 UAP Corporation. Hak cipta dilindungi Undang-Undang. Semua game gratis</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.1/dist/sweetalert2.min.js"></script>
    <script>
        // Periksa session dan tampilkan SweetAlert berdasarkan status
        <?php if (isset($_SESSION['reset_status'])): ?>
            <?php if ($_SESSION['reset_status'] == 'success'): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Reset Password Berhasil!',
                    text: 'silakan melakukan login',
                }).then((result) => {
                    window.location.href = '../auth/login.php'; // Redirect ke halaman login
                });
            <?php elseif ($_SESSION['reset_status'] == 'failed'): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Reset Password Gagal!',
                    text: 'terjadi kesalahan, silakan coba lagi',
                });
            <?php elseif ($_SESSION['reset_status'] == 'password_mismatch'): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Reset Password Gagal!',
                    text: 'password tidak sama',
                });
            <?php elseif ($_SESSION['reset_status'] == 'email_not_found'): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Reset Password Gagal!',
                    text: 'email tidak terdaftar',
                });
            <?php endif; ?>
            <?php unset($_SESSION['reset_status']); // Hapus status setelah ditampilkan ?>
        <?php endif; ?>
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>
