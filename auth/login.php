<?php
session_start();
include('../db_connect/DatabaseConnection.php');
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>

    body {
        margin: 0;
        padding: 0;
        font-family: Arial, sans-serif;
        background-color: #2C2C2C;
        color: white;

    }
    #login-section{
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        background-image: url('https://i.im.ge/2024/11/16/zTTkxF.Background.png');
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

    .container {
        flex: 1; /* Agar section login menyesuaikan tinggi */
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .login-box {
        background-color: rgba(0, 0, 0, 0.8);
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
        background-color: #007bff;
        border: none;
        padding: 10px 20px;
        font-size: 1rem;
        border-radius: 5px;
    }
    section .btn-primary:hover {
        background-color: #0056b3;
    }

    .text-section {
        padding: 40px 20px;
    }

    footer {
    
        font-size: 0.9rem;
        color: #AAA;
        text-align: center;
        padding: 30px;
        background-color: #1C1C1C;
    }

    .register-btn{
        background: linear-gradient(90deg, #1b73e8, #004ba0);
        border: none;
        color: white;
        transition: background-color 0.3s ease, transform 0.3s ease;
        
    }

        /* Efek saat hover */
        .register-btn:hover {
        background: linear-gradient(90deg, #004ba0, #1b73e8); /* Gradien berbalik */
        transform: scale(1.05); /* Sedikit memperbesar tombol */
    }

    /* Menghilangkan padding khusus footer*/
    section.text-white-5py{
        margin-bottom: 0;
    }

    .register{
        padding-bottom: 20px;
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
                <h2>Login</h2>
                <form action="#" method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="user_password" placeholder="Enter your password" required>
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Remember Me</label>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Login</button>
                </form>
                <div class="text-center mt-3">
                    <a href="#" class="text-decoration-none text-info">Forgot Password?</a>
                </div>
            </div>
        </div>
    </section>

<!-- Baru di UAP Section -->
<section class="text-white py-5" style="background-color: #1C1C1C;">
    <div class="container register">
        <div class="row">
            <!-- Kolom 1 -->
            <div class="col-md-6 d-flex flex-column justify-content-center">
                <h3 class="mb-3" style="font-size: 1.5rem; font-weight: bold;">Baru di UAP?</h3>
                <p style="color: #ccc; font-size: 1.1rem;">
                    Gratis dan mudah. Temukan ribuan game untuk dimainkan dengan jutaan teman baru.
                </p>
            </div>
            <!-- Kolom 2 -->
            <div class="col-md-6 d-flex flex-column align-items-center justify-content-center">
            <a href="..\auth\Register.php" class="btn btn-primary mb-3 px-5 py-3 register-btn">
                Buat Akun
            </a>
                <a href="#" class="text-decoration-none" style="color: #5caeff; font-size: 1rem;">Pelajari lebih lanjut tentang UAP</a>
            </div>
        </div>
    </div>
</section>




    <!-- Footer -->
    <footer class="text-center text-white" style="background-color: #1C1C1C;">
        <div class="container">
            <p>© 2024 UAP Corporation. Hak cipta dilindungi Undang-Undang.</p>
            <p>Semua game gratis</p>
        </div>

    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>

<?php
   if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $username = mysqli_real_escape_string($conn, $_POST['username']); //mysqli untuk mencegah sql injection
    $password = mysqli_real_escape_string($conn, $_POST['user_password']);
    

    $query = "SELECT * FROM users WHERE username = '$username'";
    $result = mysqli_query($conn, $query);
    //troubleshoot
    if($result){
        echo"Berhasil";
    }else{
        echo "Error: " . mysqli_error($conn);
    }
    // Ambil data hasil query
    $user = mysqli_fetch_assoc($result);

    // Validasi apakah user ditemukan
    if ($user) {
        // Verifikasi password
        if (password_verify($password, $user['password'])) {
            // Login berhasil, set session
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];

            // Redirect ke halaman utama
            header("Location: ../main_form/mainForm.php");
            exit();
        } else {  
            $error_message = "Password salah.";
        }
    } else {
        $error_message = "Username tidak ditemukan.";
    }
}
?>