<?php
// Mulai session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('../db_connect/DatabaseConnection.php');

// Define ImgBB API key and URL
define('IMGBB_API_KEY', 'cd64310f3d944ddab347166d2cd115d6'); // Replace with your ImgBB API key
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
            'key' => IMGBB_API_KEY
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
            $_SESSION['status'] = "upload";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    } else {
        $_SESSION['status'] = "file";
        header("Location: " . $_SERVER['PHP_SELF']);
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
                $_SESSION['status'] = "add";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            }
        } else {   
            $_SESSION['status'] = "add_error";
            header("Location: " . $_SERVER['PHP_SELF']);
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
        $_SESSION['status'] = "delete";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $errorMessage = "Gagal menghapus game: " . $conn->error;
        $_SESSION['status'] = "delete_error";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    }
}

// Proses jika form edit game di-submit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['edit_game_id'])) {
    $editGameId = $_POST['edit_game_id'];
    $gameName = $_POST['editGameName'];
    $gameDesc = $_POST['editGameDesc'];

    if ($editGameId) {
        // Query untuk mengambil status is_admit
        $stmt = $conn->prepare("SELECT is_admit FROM games WHERE id_game = ?");
        $stmt->bind_param("i", $editGameId);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $game = $result->fetch_assoc();
            $isAdmit = $game['is_admit'];  // Ambil nilai is_admit
        } 
    }

    // Handle cover image upload
    $coverImagePath = null;

    // Cek jika gambar baru di-upload
    if (isset($_FILES['editCoverImage']) && $_FILES['editCoverImage']['error'] == 0) {
        $imageData = base64_encode(file_get_contents($_FILES['editCoverImage']['tmp_name']));
        
        // Prepare data untuk ImgBB
        $data = [
            'image' => $imageData,
            'key' => IMGBB_API_KEY
        ];

        // Upload gambar menggunakan cURL
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, IMGBB_URL);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        // Eksekusi cURL dan ambil responsenya
        $response = curl_exec($ch);
        $responseData = json_decode($response, true);

        if (isset($responseData['data']['url'])) {
            $coverImagePath = $responseData['data']['url']; // Dapatkan URL gambar baru
        } else {
            $_SESSION['status'] = "edit_upload";
            header("Location: " . $_SERVER['PHP_SELF']);
            exit();
        }
    }

    // Jika tidak ada gambar baru, biarkan gambar lama
    if (!$coverImagePath) {
        $stmt = $conn->prepare("SELECT games_image FROM games WHERE id_game = ?");
        $stmt->bind_param("i", $editGameId);
        $stmt->execute();
        $result = $stmt->get_result();
        $game = $result->fetch_assoc();
        $coverImagePath = $game['games_image']; // Gunakan gambar lama
    }

    // Update game di database
    $stmt = $conn->prepare("UPDATE games SET game_name = ?, game_desc = ?, is_admit = ?, games_image = ? WHERE id_game = ?");
    $stmt->bind_param("ssisi", $gameName, $gameDesc, $isAdmit, $coverImagePath, $editGameId);

    if ($stmt->execute()) {
        // Update genre jika ada perubahan
        $stmt = $conn->prepare("DELETE FROM detail_genre WHERE id_game = ?");
        $stmt->bind_param("i", $editGameId);
        $stmt->execute();

        if (isset($_POST['editGameGenres'])) {
            foreach ($_POST['editGameGenres'] as $genreId) {
                $stmt = $conn->prepare("INSERT INTO detail_genre (id_game, id_genre) VALUES (?, ?)");
                $stmt->bind_param("ii", $editGameId, $genreId);
                $stmt->execute();
            }
        }
        $_SESSION['status'] = "edit";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        $_SESSION['status'] = "edit_error";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();;
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
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.1/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
                echo "<button type='button' class='btn btn-primary ms-2' data-bs-toggle='modal' data-bs-target='#editGameModal" . $game['id_game'] . "'>Edit</button>";
                echo "</form>";
                echo "</div></div></div>";
                
                $selectedGenresStmt = $conn->prepare("
                    SELECT id_genre 
                    FROM detail_genre 
                    WHERE id_game = ?
                ");
                $selectedGenresStmt->bind_param("i", $game['id_game']);
                $selectedGenresStmt->execute();
                $selectedGenresResult = $selectedGenresStmt->get_result();

                $selectedGenres = [];
                while ($selectedGenre = $selectedGenresResult->fetch_assoc()) {
                    $selectedGenres[] = $selectedGenre['id_genre'];
                }

                // Modal untuk edit game
                echo "<div class='modal fade' id='editGameModal" . $game['id_game'] . "' tabindex='-1' aria-labelledby='editGameModalLabel" . $game['id_game'] . "' aria-hidden='true'>";
                echo "<div class='modal-dialog modal-lg modal-dialog-centered'>";
                echo "<div class='modal-content'>";
                echo "<div class='modal-header'>";
                echo "<h5 class='modal-title' id='editGameModalLabel" . $game['id_game'] . "'>Edit Game</h5>";
                echo "<button type='button' class='btn-close' data-bs-dismiss='modal' aria-label='Close'></button>";
                echo "</div>";
                echo "<form action='' method='POST' enctype='multipart/form-data'>";
                echo "<div class='modal-body'>";
                echo "<input type='hidden' name='edit_game_id' value='" . $game['id_game'] . "'>";

                // Input Gambar Baru
                echo "<div class='mb-3'>";
                echo "<label for='editCoverImage" . $game['id_game'] . "' class='form-label'>Gambar Game Baru (Opsional)</label>";
                echo "<input type='file' class='form-control' id='editCoverImage" . $game['id_game'] . "' name='editCoverImage' accept='image/*'>";
                echo "</div>";

                // Input Nama Game
                echo "<div class='mb-3'>";
                echo "<label for='editGameName" . $game['id_game'] . "' class='form-label'>Nama Game</label>";
                echo "<input type='text' class='form-control' id='editGameName" . $game['id_game'] . "' name='editGameName' value='" . htmlspecialchars($game['game_name']) . "' required>";
                echo "</div>";

                // Input Deskripsi Game
                echo "<div class='mb-3'>";
                echo "<label for='editGameDesc" . $game['id_game'] . "' class='form-label'>Deskripsi Game</label>";
                echo "<textarea class='form-control' id='editGameDesc" . $game['id_game'] . "' name='editGameDesc' rows='3' required>" . htmlspecialchars($game['game_desc']) . "</textarea>";
                echo "</div>";

                // Input Genre (dibagi menjadi dua kolom)
                echo "<div class='mb-3'>";
                echo "<label for='editGameGenres" . $game['id_game'] . "' class='form-label'>Genre</label>";
                echo "<div class='row'>"; // Menggunakan row untuk membagi menjadi dua kolom

                // Kolom pertama untuk genre
                echo "<div class='col-md-6'>";
                $result = $conn->query("SELECT id_genre, genre_name FROM genre WHERE id_genre BETWEEN 1 AND 15"); // Ambil genre 16 dan seterusnya
                while ($row = $result->fetch_assoc()) {
                    $isChecked = in_array($row['id_genre'], $selectedGenres) ? 'checked' : '';
                    echo "<div class='form-check'>";
                    echo "<input class='form-check-input' type='checkbox' name='editGameGenres[]' value='" . $row['id_genre'] . "' id='editGenre" . $row['id_genre'] . "' $isChecked>";
                    echo "<label class='form-check-label' for='editGenre" . $row['id_genre'] . "'>" . $row['genre_name'] . "</label>";
                    echo "</div>";
                }
                echo "</div>"; // Tutup kolom pertama

                // Kolom kedua untuk genre
                echo "<div class='col-md-6'>";
                $result = $conn->query("SELECT id_genre, genre_name FROM genre WHERE id_genre > 15"); // Ambil genre 16 dan seterusnya
                while ($row = $result->fetch_assoc()) {
                    $isChecked = in_array($row['id_genre'], $selectedGenres) ? 'checked' : '';
                    echo "<div class='form-check'>";
                    echo "<input class='form-check-input' type='checkbox' name='editGameGenres[]' value='" . $row['id_genre'] . "' id='editGenre" . $row['id_genre'] . "' $isChecked>";
                    echo "<label class='form-check-label' for='editGenre" . $row['id_genre'] . "'>" . $row['genre_name'] . "</label>";
                    echo "</div>";
                }
                echo "</div>"; // Tutup kolom kedua

                echo "</div>"; // Tutup row
                echo "</div>"; // Tutup mb-3

                echo "</div>"; // Tutup modal-body

                echo "<div class='modal-footer'>";
                echo "<button type='button' class='btn btn-secondary' data-bs-dismiss='modal'>Tutup</button>";
                echo "<button type='submit' class='btn btn-primary'>Simpan Perubahan</button>";
                echo "</div>";

                echo "</form>";
                echo "</div>";
                echo "</div>";
                echo "</div>";
            }
            ?>
        </div>
    </div>
    </section>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.1/dist/sweetalert2.min.js"></script>
    <script>
        <?php if (isset($_SESSION['status'])): ?>
            <?php if ($_SESSION['status'] == 'upload'): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Add Game Gagal!',
                    text: 'gambar gagal diupload',
                });
            <?php elseif ($_SESSION['status'] == 'file'): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Add Game Gagal!',
                    text: 'file gambar salah',
                });
            <?php elseif ($_SESSION['status'] == 'add'): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Add Game Berhasil!',
                    text: 'game berhasil ditambahkan',
                });
            <?php elseif ($_SESSION['status'] == 'add_error'): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Add Game Gagal!',
                    text: 'game gagal ditambahkan',
                });
            <?php elseif ($_SESSION['status'] == 'delete'): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Hapus Game Berhasil!',
                    text: 'game berhasil dihapus',
                });
            <?php elseif ($_SESSION['status'] == 'delete_error'): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Hapus Game Gagal!',
                    text: 'game gagal dihapus',
                });
            <?php elseif ($_SESSION['status'] == 'edit_upload'): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Edit Game Gagal!',
                    text: 'gambar gagal diupload',
                });
            <?php elseif ($_SESSION['status'] == 'edit'): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Edit Game Berhasil!',
                    text: 'game berhasil diperbarui',
                });
            <?php elseif ($_SESSION['status'] == 'edit_error'): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Edit Game Gagal!',
                    text: 'game gagal diperbarui',
                });
            <?php endif; ?>
            <?php unset($_SESSION['status']); // Hapus status setelah ditampilkan ?>
        <?php endif; ?>
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>