<?php
// Memulai sesi dan koneksi database
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('../db_connect/DatabaseConnection.php'); // Sesuaikan dengan jalur file Anda

define('IMGBB_URL', 'https://api.imgbb.com/1/upload'); // URL ImgBB API
define('IMGBB_API_KEY', '635ce58a6dce8d81a73d9f2d6edb0e9f'); // Ganti dengan API Key Anda

$errorMessage = '';
$success = '';

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
            $errorMessage = 'cURL Error: ' . curl_error($ch);
        }
        curl_close($ch);

        // Decode respons dari ImgBB
        $responseData = json_decode($response, true);

        if (isset($responseData['data']['url'])) {
            $ImagePath = $responseData['data']['url']; // URL gambar yang diunggah
        } else {
            $errorMessage = "Gagal mengupload gambar: " . ($responseData['error']['message'] ?? 'Unknown error');
        }
    } else {
        $errorMessage = "File tidak valid. Harap unggah gambar.";
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
                $success = "Foto profil berhasil diperbarui!";
            } else {
                $errorMessage = "Gagal memperbarui foto profil di database.";
            }
        } else {
            $errorMessage = "Akun tidak ditemukan di sistem.";
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Change Profile Picture</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #2C2C2C; color: white; font-family: Arial, sans-serif; }
        .container { display: flex; justify-content: center; align-items: center; height: 100vh; }
        .form-box { background-color: rgba(0, 0, 0, 0.8); padding: 30px 25px; border-radius: 10px; width: 100%; max-width: 400px; }
        .btn-primary { background-color: #007bff; border: none; width: 100%; }
        .btn-primary:hover { background-color: #0056b3; }
        .btn-secondary { background-color: #6c757d; border: none; width: 100%; margin-top: 10px; }
        .btn-secondary:hover { background-color: #5a6268; }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-box">
            <h2>Change Profile Picture</h2>
            <?php if ($errorMessage): ?>
                <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <a href="userProfile.php" class="btn btn-secondary">Back to Profile</a>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-3">
                    <label for="user_profile" class="form-label">Upload New Picture</label>
                    <input type="file" class="form-control" name="user_profile" id="user_profile" required>
                </div>
                <button type="submit" class="btn btn-primary">Update Picture</button>
            </form>
        </div>
    </div>
</body>
</html>