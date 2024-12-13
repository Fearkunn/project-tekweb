<?php
// Memulai sesi dan koneksi database
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

include('../db_connect/DatabaseConnection.php'); // Sesuaikan dengan jalur file Anda

define('IMGBB_URL', 'https://api.imgbb.com/1/upload'); // URL ImgBB API
define('IMGBB_API_KEY', 'cd64310f3d944ddab347166d2cd115d6'); // Ganti dengan API Key Anda

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['user_profile'])) {
    $user_profile = $_FILES['user_profile'];
    $username = $_SESSION['username'];

    // Validasi apakah file adalah gambar
    if ($user_profile['error'] === 0 && getimagesize($user_profile['tmp_name']) !== false) {
        $ImagePath = null;

        // Menggunakan ImgBB API untuk mengupload gambar
        $imageData = base64_encode(file_get_contents($user_profile['tmp_name']));

        // Data untuk ImgBB
        $data = [
            'image' => $imageData,
            'key' => IMGBB_API_KEY
        ];

        // Kirim data ke ImgBB menggunakan cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, IMGBB_URL);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $_SESSION['Send'] = ['type' => 'error', 'message' => "gagal mengupload gambar"];
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
        curl_close($ch);

        // Decode respons dari ImgBB
        $responseData = json_decode($response, true);

        if (isset($responseData['data']['url'])) {
            $ImagePath = $responseData['data']['url']; // URL gambar yang diunggah
        } else {
            $_SESSION['Send'] = ['type' => 'error', 'message' => "gagal mengupload gambar"];
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    } else {
        $_SESSION['Send'] = ['type' => 'error', 'message' => "file tidak valid, harap mengupload gambar"];
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }

    // Simpan URL gambar ke database
    if ($ImagePath) {
        // Cek apakah username ada di tabel `users` atau `publisher`
        $table = '';
        $column = '';

        // Cek di tabel `users`
        $user_query = "SELECT id_user FROM users WHERE username = ?";
        $user_stmt = $conn->prepare($user_query);
        $user_stmt->bind_param("s", $username);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();

        if ($user_result->num_rows > 0) {
            // Username ditemukan di tabel `users`
            $table = "users";
            $column = "id_user";
        } else {
            // Periksa di tabel `publisher`
            $publisher_query = "SELECT id_publisher FROM publisher WHERE publisher_name = ?";
            $publisher_stmt = $conn->prepare($publisher_query);
            $publisher_stmt->bind_param("s", $username);
            $publisher_stmt->execute();
            $publisher_result = $publisher_stmt->get_result();

            if ($publisher_result->num_rows > 0) {
                // Username ditemukan di tabel `publisher`
                $table = "publisher";
                $column = "id_publisher";
            }
        }

        if ($table) {
            // Jika ditemukan di `users`, simpan di user_profile, jika di `publisher`, simpan di publisher_logo
            $update_query = "UPDATE $table SET " . ($table == 'users' ? 'user_profile' : 'publisher_logo') . " = ? WHERE $column = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("si", $ImagePath, $_SESSION['user_id']);
            if ($update_stmt->execute()) {
                $_SESSION['Send'] = ['type' => 'success', 'message' => 'foto profile berhasil diperbarui', 'redirect' => '../main_form/userProfile.php'];
            } else {
                $_SESSION['Send'] = ['type' => 'error', 'message' => 'gagal memperbarui foto profile'];
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
        } else {
            $_SESSION['Send'] = ['type' => 'error', 'message' => 'akun tidak ditemukan di sistem'];
            header("Location: " . $_SERVER['PHP_SELF']);
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
    <title>Edit Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.1/dist/sweetalert2.min.css" rel="stylesheet">
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
        #picture-section{
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
            font-weight: bold;
            font-size: 1.25rem;
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
                    title: "<?= $_SESSION['Send']['type'] === 'success' ? 'Edit Profile Berhasil!' : 'Edit Profile Gagal!' ?>",
                    text: "<?= $_SESSION['Send']['message'] ?>",
                    icon: "<?= $_SESSION['Send']['type'] ?>",
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

    <section id="picture-section">
        <div class="container">
            <div class="form-box">
                <h2 class="pb-3">Edit Profile Picture</h2>
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3 pb-2">
                        <label for="user_profile" class="form-label">Upload Profile Picture Baru</label>
                        <input type="file" class="form-control" name="user_profile" id="user_profile" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Update Profile Picture</button>
                </form>
            </div>
        </div>
    </section>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.1/dist/sweetalert2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>