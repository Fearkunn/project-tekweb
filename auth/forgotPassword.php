<?php
session_start();

// Jika pengguna sudah login, arahkan ke halaman dashboard
if (isset($_SESSION['user_id'])) {
    header("Location: ../main_form/mainForm.php");
    exit();
}

// Include koneksi database
include('../db_connect/DatabaseConnection.php');

// Include PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../PHPMailer/src/Exception.php';
require '../PHPMailer/src/PHPMailer.php';
require '../PHPMailer/src/SMTP.php';

// Atur langkah proses
if (!isset($_SESSION['step'])) {
    $_SESSION['step'] = 1; // Default untuk langkah pertama
}

$error = ''; // Pesan error

// Proses jika form telah dikirim
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Langkah 1: Memeriksa email
    if ($_SESSION['step'] == 1 && isset($_POST['user_email'])) {
        $email = $_POST['user_email'];

        // Validasi email
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            // Periksa apakah email ada di database
            $stmt = $conn->prepare("SELECT * FROM users WHERE user_email = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                // Jika email ditemukan, buat kode reset password
                $Kode = random_int(100000, 999999);

                // Simpan kode dan waktu ke session
                $_SESSION['reset_Kode'] = $Kode;
                $_SESSION['reset_Kode_time'] = time();
                $_SESSION['email'] = $email;

                // Kirim email dengan PHPMailer
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'uap.company2023@gmail.com';
                    $mail->Password = 'lmjz izih evfk pbve';
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                    $mail->Port = 587;

                    $mail->setFrom('uap.company2023@gmail.com', 'Uap');
                    $mail->addAddress($email);

                    $mail->isHTML(true);
                    $mail->Subject = "Reset Password Request - Your Verification Code";
                    $mail->Body = "
                        <p>Halo,</p>
                        <p>Kode verifikasi Anda adalah:</p>
                        <h1 style='text-align: center; color: #007bff;'>{$Kode}</h1>
                        <p>Gunakan kode ini untuk melanjutkan proses reset password.</p>
                    ";

                    $mail->send();
                    $_SESSION['step'] = 2; // Beralih ke langkah kedua
                } catch (Exception $e) {
                    $error = "Gagal mengirim email: {$mail->ErrorInfo}";
                }
            } else {
                $error = "Email tidak ditemukan.";
            }
        } else {
            $error = "Format email tidak valid.";
        }
    }

    // Langkah 2: Memeriksa kode
    if ($_SESSION['step'] == 2 && isset($_POST['Kode'])) {
        $Kode = $_POST['Kode'];

        // Validasi kode
        if (isset($_SESSION['reset_Kode']) && $Kode == $_SESSION['reset_Kode']) {
            if (time() - $_SESSION['reset_Kode_time'] < 600) { // 600 detik = 10 menit
                // Hapus data session terkait kode
                $_SESSION['reset_Kode'] = null;
                $_SESSION['reset_Kode_time'] = null;
                $_SESSION['step'] = null;
                //$_SESSION['user_email'] = $email;

                // Redirect ke halaman reset password
                header("Location: resetPassword.php");
                exit();
            } else {
                $error = "Kode telah kadaluarsa.";
            }
        } else {
            $error = "Kode tidak valid.";
        }
    }
}
?>


<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Forgot Password</title>
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

    <!-- Section Forgot Password -->
    <section id="forgot-password-section">
        <div class="container">
            <div class="forgot-password-box">
                <h2>Forgot Password</h2>
                <?php if ($_SESSION['step'] == 1): ?>
                    <form action="#" method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="user_email" placeholder="Enter your email" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Submit</button>
                    </form>
                <?php elseif ($_SESSION['step'] == 2): ?>
                    <form action="#" method="POST">
                        <div class="mb-3">
                            <label for="Kode" class="form-label">Kode</label>
                            <input type="text" class="form-control" id="Kode" name="Kode" placeholder="Enter your Kode" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Submit</button>
                    </form>
                <?php endif; ?>


                <div class="text-center mt-3">
                    <a href="..\auth\Login.php" class="text-decoration-none text-info">Remember your password? Login</a>
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