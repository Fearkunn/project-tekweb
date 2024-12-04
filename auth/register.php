<?php
session_start();
include('../db_connect/DatabaseConnection.php');
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Register</title>
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

    .register-btn {
        background: linear-gradient(90deg, #1b73e8, #004ba0);
        border: none;
        color: white;
        transition: background-color 0.3s ease, transform 0.3s ease;
    }

    .register-btn:hover {
        background: linear-gradient(90deg, #004ba0, #1b73e8);
        transform: scale(1.05);
    }

    section.text-white-5py {
        margin-bottom: 0;
    }

    .register {
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

    <!-- Section Register -->
    <section id="register-section">
        <div class="container">
            <div class="register-box">
                <h2>Register</h2>
                <form action="#" method="POST">
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control" id="username" name="username" placeholder="Enter your username" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control" id="email" name="user_email" placeholder="Enter your email" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password" name="user_password" placeholder="Enter your password" required>
                    </div>
                    <div class="mb-3">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">Register</button>
                </form>
                <div class="text-center mt-3">
                    <a href="..\auth\Login.php" class="text-decoration-none text-info">Already have an account? Login</a>
                </div>
            </div>
        </div>
    </section>

    

    <!-- Footer -->
    <footer class="text-center text-white p-4" style="background-color: #1C1C1C;">
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
        $username = mysqli_real_escape_string($conn, $_POST['username']);
        $email = mysqli_real_escape_string($conn, $_POST['user_email']);
        $password = mysqli_real_escape_string($conn, $_POST['user_password']);
        $confirm_password = mysqli_real_escape_string($conn, $_POST['confirm_password']);

        if ($confirm_password !== $confirm_password) {
            $error_message = "Password Tidak Sama";
        }else{
            //check validasi email ada atau tidak di db
            $query = "SELECT * FROM users WHERE user_email = '$email'";
            
            $result = mysqli_query($conn, $query);
            if (mysqli_num_rows($result) > 0) {
                $error_message = "Email sudah terdaftar";
                
            }else{
                $query = "SELECT * FROM users WHERE username = '$username'";
                $result = mysqli_query($conn, $query);
                if (mysqli_num_rows($result) > 0) {
                $error_message = "Username sudah ada";
                }else{
                    $id_query = "SELECT MAX(id_user) AS max_id FROM users";
                    $id_result = mysqli_query($conn, $id_query);
                    $id_row = mysqli_fetch_assoc($id_result);
                    $new_user_id = $id_row['max_id'] + 1;
                    
                    // Increment the max_id by 1 for the new user
                    //mengirim password dengan password_hash agar terenkripsi
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $insert_query = "INSERT INTO users (id_user,username, user_email, user_password) VALUES ('$new_user_id','$username', '$email', '$hashed_password')";
                    if (mysqli_query($conn, $insert_query)) {
                        echo "<script>console.log('Insert query done');</script>";
                        $_SESSION['username'] = $username;  // Menyimpan username di session
                        header("Location: ../main_form/mainForm.php");  // Redirect ke halaman utama setelah registrasi berhasil
                        exit();
                    } else {
                        $error_message = "Terjadi kesalahan, coba lagi nanti.";
                    }
                }
            }  
        }
    }
?>