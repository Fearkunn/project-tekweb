<?php
// Mulai session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('../db_connect/DatabaseConnection.php');

// Define ImgBB API key and URL
define('IMGBB_API_KEY', 'YOUR_IMGBB_API_KEY'); // Replace with your ImgBB API key
define('IMGBB_URL', 'https://api.imgbb.com/1/upload');

// Proses jika form ditambahkan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gameName'])) {
    // Proses tambah game
    $gameName = $_POST['gameName'];
    $gameDesc = $_POST['gameDesc'];
    $coverImage = $_FILES['coverImage'];
    $gameGenres = isset($_POST['gameGenres']) ? $_POST['gameGenres'] : [];

    // Ambil id_publisher berdasarkan user_id dari session
    $userId = $_SESSION['username'];
    $stmt = $conn->prepare("SELECT id_publisher FROM publisher WHERE publisher_name = ?");
    $stmt->bind_param("s", $userId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $idPublisher = $row['id_publisher'];
    } else {
        die("Publisher tidak ditemukan untuk pengguna ini.");
    }
    

    // Proses upload gambar cover ke ImgBB
    $coverImagePath = null;

    // Check if the file was uploaded without errors
    if (isset($coverImage) && $coverImage['error'] == 0) {
        $imageData = base64_encode(file_get_contents($coverImage['tmp_name']));
        
        // Prepare data for ImgBB
        $data = [
            'image' => $imageData,
            'key' => '635ce58a6dce8d81a73d9f2d6edb0e9f'
        ];

        // Use cURL to upload the image
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, IMGBB_URL);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Execute cURL and get the response
        $response = curl_exec($ch);

        // Decode the response
        $responseData = json_decode($response, true);

        if (isset($responseData['data']['url'])) {
            $coverImagePath = $responseData['data']['url']; // Get the URL of the uploaded image
        } else {
            $_SESSION['Send'] = ['type' => 'error', 'message' => 'Gagal mengupload gambar:' . $responseData['message']];
            header('Location: ../main_form/addGame.php');
            exit();
        }
    } else {
        $_SESSION['Send'] = ['type' => 'error', 'message' => 'Gambar tidak valid.'];
        header('Location: ../main_form/addGame.php');
        exit();
    }

    // Simpan game ke database jika gambar berhasil diupload
    if ($coverImagePath) {
        $stmt = $conn->prepare("INSERT INTO games (game_name, game_desc, is_admit, release_date, id_publisher, games_image) VALUES (?, ?, ?, NOW(), ?, ?)");
        $isAdmit = false; // Default status
        $stmt->bind_param("ssiis",$gameName, $gameDesc, $isAdmit, $idPublisher, $coverImagePath);

        if ($stmt->execute()) {
            $lastInsertId = $stmt->insert_id; // Dapatkan ID game terakhir yang ditambahkan
            

            // Simpan genre ke database
            if ($gameGenres) {
                foreach ($gameGenres as $genreId) {
                    $stmt = $conn->prepare("INSERT INTO detail_genre (id_game, id_genre) VALUES (?, ?)");
                    $stmt->bind_param("ii", $lastInsertId, $genreId);
                    $stmt->execute();
                }
                $_SESSION['Send'] = ['type' => 'success', 'message' => 'Game berhasil ditambahkan.'];
                header('Location: ../main_form/addGame.php');
                exit();
            }
        } else {   
            $_SESSION['Send'] = ['type' => 'error', 'message' => 'Gagal menambahkan game:' . $conn->error];
            header('Location: ../main_form/addGame.php');
            exit();
        }
    }
}

// Proses jika game dihapus
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_game_id'])) {
    $deleteGameId = $_POST['delete_game_id'];
    $stmt = $conn->prepare("DELETE FROM detail_genre WHERE id_game = ?");
    $stmt->bind_param("i", $deleteGameId);
    if ($stmt->execute()) {
        $successMessage = "Detail berhasil dihapus.";
        
    } else {
        $errorMessage = "Gagal menghapus detail: " . $conn->error;
    }
    $stmt = $conn->prepare("DELETE FROM games WHERE id_game = ?");
    $stmt->bind_param("i", $deleteGameId);
    if ($stmt->execute()) {
        $_SESSION['Send'] = ['type' => 'success', 'message' => 'Game berhasil dihapus.'];
        header('Location: ../main_form/addGame.php');
        exit();
    } else {
        $errorMessage = "Gagal menghapus game: " . $conn->error;
        $_SESSION['Send'] = ['type' => 'error', 'message' => 'Gagal menghapus game: ' . $conn->error];
        header('Location: ../main_form/addGame.php');
        exit();
    }
}

// Proses jika form edit game di-submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_game_id'])) {
    $editGameId = $_POST['edit_game_id'];
    $gameName = $_POST['editGameName'];
    $gameDesc = $_POST['editGameDesc'];
    $isAdmit = isset($_POST['editIsAdmit']) ? true : false; // Misalnya Anda ingin mengedit status persetujuan

    $stmt = $conn->prepare("UPDATE games SET game_name = ?, game_desc = ?, is_admit = ? WHERE id_game = ?");
    $stmt->bind_param("ssii", $gameName, $gameDesc, $isAdmit, $editGameId);
    if ($stmt->execute()) {
        $_SESSION['Send'] = ['type' => 'success', 'message' => 'Game berhasil diperbarui.'];
        header('Location: ../main_form/addGame.php');
        exit();
    } else {
        $_SESSION['Send'] = ['type' => 'error', 'message' => 'Gagal memperbarui game:' . $conn->error];
        header('Location: ../main_form/addGame.php');
        exit();
    }
}

// Ambil data game berdasarkan id_publisher dari session
$userId = $_SESSION['username'];
$stmt = $conn->prepare("SELECT id_publisher FROM publisher WHERE publisher_name = ?");
$stmt->bind_param("s", $userId);
$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $idPublisher = $row['id_publisher'];
} else {
    die("Publisher tidak ditemukan untuk pengguna ini.");
}

$gamesStmt = $conn->prepare("
    SELECT 
        g.id_game, 
        g.game_name, 
        g.game_desc, 
        g.games_image, 
        g.is_admit, 
        GROUP_CONCAT(DISTINCT gen.genre_name SEPARATOR ', ') AS genres
    FROM games g
    LEFT JOIN detail_genre dg ON g.id_game = dg.id_game
    LEFT JOIN genre gen ON dg.id_genre = gen.id_genre
    WHERE g.id_publisher = ?
    GROUP BY g.id_game
");
$gamesStmt->bind_param("i", $idPublisher);
$gamesStmt->execute();
$games = $gamesStmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../assets/UAP.ico" type="image/x-icon">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <title>Add Game</title>
    <style>
    body {
        margin: 0;
        padding: 0;
        font-family: Arial, sans-serif;
        background-color: #2C2C2C;

    }
    #add-game-section{
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
    .btn {
        cursor: pointer; 
        transition: transform 0.2s; 
    }
    .btn:hover { 
        transform: scale(1.05); 
    }
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
                    }).then(() => {
                        <?php if ($_SESSION['Send']['type'] === 'success' && isset($_SESSION['Send']['redirect'])): ?>
                            window.location.href = "<?= $_SESSION['Send']['redirect'] ?>";
                        <?php endif; ?>
                    });
                </script>
        <?php unset($_SESSION['Send']); ?>
    <?php endif; ?>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand mx-auto" href="../main_form/mainForm.php">
                <img src="../assets/UapLogoText.svg" alt="UapLogo">
            </a>
        </div>
    </nav>

    <section id="add-game-section">
    <div class="container pt-5">
        <!-- Tombol Tambah Game -->
        <div class="btn btn-secondary" data-bs-toggle="modal" data-bs-target="#gameModal">Tambahkan game baru</div>
        

        <!-- Modal untuk tambah game -->
        <div class="modal fade" id="gameModal" tabindex="-1" aria-labelledby="gameModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="gameModalLabel">Tambah Game</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="coverImage" class="form-label">Gambar Game</label>
                                <input type="file" class="form-control" id="coverImage" name="coverImage" accept="image/*" required>
                            </div>
                            <div class="mb-3">
                                <label for="gameName" class="form-label">Nama Game</label>
                                <input type="text" class="form-control" id="gameName" name="gameName" required>
                            </div>
                            <div class="mb-3">
                                <label for="gameDesc" class="form-label">Deskripsi Game</label>
                                <textarea class="form-control" id="gameDesc" name="gameDesc" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="gameGenres" class="form-label">Pilih Genre</label>
                                <div>
                                <?php
                                // Ambil genre dari database
                                $result = $conn->query("SELECT id_genre, genre_name FROM genre");
                                $genres = [];

                                // Simpan semua genre dalam array untuk pemrosesan
                                while ($row = $result->fetch_assoc()) {
                                    $genres[] = $row;
                                }

                                // Hitung jumlah genre
                                $totalGenres = count($genres);
                                $half = ceil($totalGenres / 2); // Tentukan titik tengah untuk membagi dua kolom

                                echo "<div class='row'>";

                                // Kolom pertama
                                echo "<div class='col-md-6'>";
                                for ($i = 0; $i < $half; $i++) {
                                    echo "<div class='form-check'>";
                                    echo "<input class='form-check-input' type='checkbox' name='gameGenres[]' value='" . $genres[$i]['id_genre'] . "' id='genre" . $genres[$i]['id_genre'] . "'>";
                                    echo "<label class='form-check-label' for='genre" . $genres[$i]['id_genre'] . "'>" . $genres[$i]['genre_name'] . "</label>";
                                    echo "</div>";
                                }
                                echo "</div>";

                                // Kolom kedua
                                echo "<div class='col-md-6'>";
                                for ($i = $half; $i < $totalGenres; $i++) {
                                    echo "<div class='form-check'>";
                                    echo "<input class='form-check-input' type='checkbox' name='gameGenres[]' value='" . $genres[$i]['id_genre'] . "' id='genre" . $genres[$i]['id_genre'] . "'>";
                                    echo "<label class='form-check-label' for='genre" . $genres[$i]['id_genre'] . "'>" . $genres[$i]['genre_name'] . "</label>";
                                    echo "</div>";
                                }
                                echo "</div>";

                                echo "</div>";
                                ?>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" class="btn btn-primary">Tambah</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Daftar Game -->
        <h3 class="mt-4 text-light">Daftar Game:</h3>
        <div class="row row-cols-3">
            <?php
            while ($game = $games->fetch_assoc()) {
                echo "<div class='col mb-4 mt-3'>";
                echo "<div class='card text-bg-dark h-100'>";
                echo "<img src='" . $game['games_image'] . "' alt='Cover' class='card-img-top' style='height: 200px; object-fit: cover;'>";
                echo "<div class='card-body'>";
                echo "<h5 class='card-title'>" . $game['game_name'] . "</h5>";
                echo "<p class='card-text'>" . $game['game_desc'] . "</p>";
                echo "<p class='card-text'>Genre: " . (htmlspecialchars($game['genres']) ?: 'No genre specified') . "</p>";
                // Indikator Status
                $statusClass = $game['is_admit'] ? 'text-success' : 'text-danger'; // Menggunakan warna hijau untuk approved dan merah untuk rejected
                $statusText = $game['is_admit'] ? 'Sudah Diterima' : 'Belum Diterima';
                echo "<p class='$statusClass pb-3'>$statusText</p>";

                echo "<form method='POST'>";
                echo "<button type='submit' name='delete_game_id' value='" . $game['id_game'] . "' class='btn btn-danger'>Hapus</button>";
                echo "</form>";
                echo "</div></div></div>";
            }
            ?>
        </div>
    </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>