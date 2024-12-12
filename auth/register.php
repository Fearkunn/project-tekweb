<?php
    session_start();
    include('../db_connect/DatabaseConnection.php');
    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $email = mysqli_real_escape_string($conn, $_POST['user_email']);
        $password = mysqli_real_escape_string($conn, $_POST['user_password']);
        $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);

        if ($confirm_password !== $password) {
            $_SESSION['error_message'] = "password tidak sama";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        } else {
            if (strpos($email, '@publisher') !== false) {
                $query = "SELECT * FROM publisher WHERE publisher_email = '$email'";
                $result = mysqli_query($conn, $query);
                if (mysqli_num_rows($result) > 0) {
                    $_SESSION['error_message'] = "publisher dengan email yang sama sudah ada";
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                } else {
                    $query = "SELECT * FROM publisher WHERE publisher_name = '$username'";
                    $result = mysqli_query($conn, $query);
                    if (mysqli_num_rows($result) > 0) {
                        $_SESSION['error_message'] = "publisher dengan nama yang sama sudah ada";
                        header("Location: " . $_SERVER['PHP_SELF']);
                        exit();
                    } else {
                        $id_query = "SELECT MAX(id_publisher) AS max_id FROM publisher";
                        $id_result = mysqli_query($conn, $id_query);
                        $id_row = mysqli_fetch_assoc($id_result);
                        $new_publisher_id = $id_row['max_id'] + 1;
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $insert_query = "INSERT INTO publisher (id_publisher, publisher_name, publisher_password, publisher_email) 
                                        VALUES ('$new_publisher_id', '$username', '$hashed_password', '$email')";
                        if (mysqli_query($conn, $insert_query)) {
                            $_SESSION['user_id'] = $new_publisher_id;
                            $_SESSION['username'] = $username; 
                            $_SESSION['role_user'] = 'PUBLISHER';
                            header("Location: ..\main_form\mainForm.php");
                            exit();
                        } else {
                            $_SESSION['error_message'] = "terjadi kesalahan, silakan coba lagi";
                            header("Location: " . $_SERVER['PHP_SELF']);
                            exit();
                        }
                    }
            }
         } else {
                $query = "SELECT * FROM users WHERE user_email = '$email'";
                $result = mysqli_query($conn, $query);
                if (mysqli_num_rows($result) > 0) {
                    $_SESSION['error_message'] = "user dengan email yang sama sudah ada";
                    header("Location: " . $_SERVER['PHP_SELF']);
                    exit();
                } else {
                    $query = "SELECT * FROM users WHERE username = '$username'";
                    $result = mysqli_query($conn, $query);
                    if (mysqli_num_rows($result) > 0) {
                        $_SESSION['error_message'] = "user dengan nama yang sama sudah ada";
                        header("Location: " . $_SERVER['PHP_SELF']);
                        exit();
                    } else {
                        $id_query = "SELECT MAX(id_user) AS max_id FROM users";
                        $id_result = mysqli_query($conn, $id_query);
                        $id_row = mysqli_fetch_assoc($id_result);
                        $new_user_id = $id_row['max_id'] + 1;
                        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                        $insert_query = "INSERT INTO users (id_user, username, user_email, user_password,role_user) 
                                         VALUES ('$new_user_id', '$username', '$email', '$hashed_password','USER')";
                        if (mysqli_query($conn, $insert_query)) {
                            $_SESSION['user_id'] = $new_user_id;
                            $_SESSION['username'] = $username; 
                            $_SESSION['role_user'] = 'USER';
                            header("Location: ..\main_form\mainForm.php");
                            exit();
                        } else {
                            $_SESSION['error_message'] = "terjadi kesalahan, silakan coba lagi";
                            header("Location: " . $_SERVER['PHP_SELF']);
                            exit();
                        }
                    }
                }
            }
        }
    }
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register</title>
    <link rel="icon" href= "../assets/UAP.ico" type="image/x-icon"> 
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.1/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>

    body {
        margin: 0;
        padding: 0;
        font-family: Arial, sans-serif;
        background-color: #2C2C2C;
        color: white;
    }

    #register-section {
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

    .container {
        flex: 1;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .register-box {
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

    <!-- Section Register -->
    <section id="register-section">
        <div class="container">
            <div class="register-box">
                <h2 class="pb-3">Register Akun</h2>
                <form action="#" method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Masukkan username Anda" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="user_email" placeholder="Masukkan email Anda" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="user_password" placeholder="Masukkan password Anda" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Konfirmasi password Anda" required>
                    </div>
                    <div class="pt-4">
                        <button type="submit" class="btn btn-primary w-100">Register</button>
                    </div>
                </form>
                <div class="text-center mt-4">
                    <a href="..\auth\Login.php" class="text-decoration-none text-info">Sudah punya akun? Login</a>
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
        <?php if (isset($_SESSION['error_message'])): ?>
            let errorMessage = "<?php echo $_SESSION['error_message']; ?>";
            Swal.fire({
                icon: 'error',
                title: 'Register Gagal!',
                text: errorMessage,
            });
            <?php unset($_SESSION['error_message']); ?>
        <?php endif; ?>
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>