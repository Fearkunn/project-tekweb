<?php
session_start();
include('../db_connect/DatabaseConnection.php');

// Login handler
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['user_password']);
    if ($username == $password && $username == 'admin') {
        $_SESSION['role_user'] = 'admin';
        $_SESSION['status'] = "admin";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    $stmt = $conn->prepare("SELECT * FROM publisher WHERE publisher_name = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if($user && password_verify($password, $user['publisher_password'])){
        $_SESSION['user_id'] = $user['id_publisher'];
        $_SESSION['username'] = $user['publisher_name'];
        $_SESSION['role_user'] = 'PUBLISHER';
        $_SESSION['status'] = "publisher";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }else{

        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user = $result->fetch_assoc();

        if ($user && password_verify($password, $user['user_password'])) {
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role_user'] = 'USER';    
            $_SESSION['status'] = "user";       
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            $_SESSION['status'] = "gagal";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }
}
?>


<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <link rel="icon" href= "../assets/UAP.ico" type="image/x-icon"> 
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.1/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>

    body {
        margin: 0;
        padding: 0;
        font-family: Arial, sans-serif;
        background-color: #0f1a23;
        color: white;

    }
    #login-section{
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        background-image: url('../assets/login.png');
        background-size: cover;
        background-repeat: no-repeat;
        background-position: center top;
        margin: 0;
        padding: 0;
    }
    .navbar {
        background-color: #0f1a23;
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

    .login-box {
        background-color: #0f1a23;
        padding: 30px 25px;
        border-radius: 10px;
        color: white;
        width: 100%;
        max-width: 400px;
        margin: 10px 0;
    }
    section {
        font-size: 1rem;
        line-height: 1.6;
        padding: 30px 0;
        margin:0;
    }
    section h3 {
        font-size: 1.5rem;
        font-weight: bold;
        margin-bottom: 20px;
    }
    section p {
        color: #AAA;
        margin-bottom: 15px;
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
        background-color: #0f1a23;
    }
    .btn-primary {
        background-color: #1b6ca8; /* Biru terang untuk kontras */
        border-color: #1b6ca8;
        color: white;
        font-weight: bold;
    }

    .btn-primary:hover {
        background-color: #145582; /* Warna lebih gelap untuk efek hover */
        border-color: #145582;
    }
    .text-info:hover {
        color: white !important;
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

    <!-- Section Login -->
    <section id="login-section">
        <div class="container">
            <div class="login-box">
                <h2 class="pb-3">Login Akun</h2>
                <form action="#" method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username Anda" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="user_password" placeholder="Masukkan password Anda" required>
                    </div>
                    <div class="pt-4">
                        <button type="submit" class="btn btn-primary w-100">Login</button>
                    </div>
                </form>
                <div class="text-center mt-4">
                    <a href="..\auth\forgotPassword.php" class="text-decoration-none text-info">Lupa Password?</a>
                </div>
            </div>
        </div>
    </section>

<!-- Baru di UAP Section -->
<section class="text-white pt-5 pb-1">
    <div class="container">
        <div class="row">
            <div class="col-md-9 d-flex flex-column justify-content-center">
                <h3 class="mb-3" style="font-size: 1.5rem; ">Baru di UAP?</h3>
                <p style="color: #ccc; font-size: 1.1rem;">
                    Gratis dan mudah. Temukan ribuan game untuk dimainkan dengan jutaan teman baru.
                </p>
            </div>
            <div class="col-md-3 d-flex flex-column justify-content-center">
            <a href="..\auth\Register.php" class="btn btn-primary mb-3 py-3">
                Buat Akun
            </a>
                
            </div>
        </div>
    </div>
</section>




    <!-- Footer -->
    <footer class="text-center text-white">
        <div class="container">
            <p style="color: #ccc;">© 2024 UAP Corporation. Hak cipta dilindungi Undang-Undang. Semua game gratis</p>
        </div>

    </footer>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.1/dist/sweetalert2.min.js"></script>
    <script>
        <?php if (isset($_SESSION['status'])): ?>
            <?php if ($_SESSION['status'] == 'user'): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Login User Berhasil!',
                    text: 'selamat menikmati UAP',
                }).then((result) => {
                    window.location.href = '../main_form/mainForm.php'; // Redirect ke halaman login
                });
            <?php elseif ($_SESSION['status'] == 'publisher'): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Login Publisher Berhasil!',
                    text: 'selamat menikmati UAP',
                }).then((result) => {
                    window.location.href = '../main_form/mainForm.php'; // Redirect ke halaman login
                });
            <?php elseif ($_SESSION['status'] == 'admin'): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Login Admin Berhasil!',
                    text: 'selamat bekerja',
                }).then((result) => {  
                    window.location.href = '../main_form/admin.php'; // Redirect ke halaman login
                });
            <?php elseif ($_SESSION['status'] == 'gagal'): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Login Gagal!',
                    text: 'username / password salah',
                });
            <?php endif; ?>
            <?php unset($_SESSION['status']); // Hapus status setelah ditampilkan ?>
        <?php endif; ?>
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>