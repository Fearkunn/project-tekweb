<?php
// Kode sebelumnya untuk memulai session dan koneksi database
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('../db_connect/DatabaseConnection.php'); // Pastikan ini sesuai dengan jalur file Anda

define('IMGBB_URL', 'https://api.imgbb.com/1/upload'); // Definisikan URL ImgBB

// Proses pengunggahan file
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['user_profile'])) {
    $user_profile = $_FILES['user_profile'];
    $username = $_SESSION['username'];
    $user_id = $_SESSION['user_id'];
    
    // Validasi apakah file adalah gambar
    if ($user_profile['error'] == 0) {
        $ImagePath = null;

        // Menggunakan ImgBB API untuk mengupload gambar
        $imageData = base64_encode(file_get_contents($user_profile['tmp_name']));
        
        // Prepare data for ImgBB
        $data = [
            'image' => $imageData,
            'key' => '635ce58a6dce8d81a73d9f2d6edb0e9f' // Ganti dengan API Key ImgBB Anda
        ];

        // Use cURL to upload the image
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, IMGBB_URL);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute cURL and get the response
        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            $errorMessage = 'cURL Error: ' . curl_error($ch);
        }
        curl_close($ch);

        // Decode the response
        $responseData = json_decode($response, true);

        if (isset($responseData['data']['url'])) {
            $ImagePath = $responseData['data']['url']; // Get the URL of the uploaded image
        } else {
            $errorMessage = "Gagal mengupload gambar: " . (isset($responseData['message']) ? $responseData['message'] : 'Unknown error');
        }
    } else {
        $errorMessage = "Gambar tidak valid.";
    }

    // Simpan path gambar ke database
    if ($ImagePath) {
        $update_query = "UPDATE users SET user_profile = ? WHERE id_user = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("si", $ImagePath, $_SESSION['user_id']);
        if ($update_stmt->execute()) { // Memanggil execute sebagai fungsi
            $success = "Foto profil berhasil diperbarui!";

        } else {
            $error = "Gagal memperbarui foto profil di database.";
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
            <?php if (isset($errorMessage)): ?>
                <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
            <?php endif; ?>
            <?php if (isset($success)): ?>
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