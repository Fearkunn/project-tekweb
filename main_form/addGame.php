<?php
// Mulai session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('../db_connect/DatabaseConnection.php');

// Proses jika form ditambahkan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gameName'])) {
    // Proses tambah game
    $gameName = $_POST['gameName'];
    $gameDesc = $_POST['gameDesc'];
    $coverImage = $_FILES['coverImage'];
    $gameGenres = isset($_POST['gameGenres']) ? $_POST['gameGenres'] : [];

    // Proses upload gambar cover
    $uploadDir = '../uploads/'; // Pastikan folder ini ada dan dapat ditulis
    $coverImagePath = $uploadDir . basename($coverImage['name']);
    move_uploaded_file($coverImage['tmp_name'], $coverImagePath);

    // Simpan game ke database
    $stmt = $conn->prepare("INSERT INTO games (game_name, game_desc, games_image, is_admit) VALUES (?, ?, ?, ?)");
    $isAdmit = true; // Default status
    $stmt->bind_param("sssi", $gameName, $gameDesc, $coverImagePath, $isAdmit);

    if ($stmt->execute()) {
        $successMessage = "Game berhasil ditambahkan.";
        $lastInsertId = $stmt->insert_id; // Dapatkan ID game terakhir yang ditambahkan

        // Simpan genre ke database
        foreach ($gameGenres as $genreId) {
            $stmtGenre = $conn->prepare("INSERT INTO game_genre (id_game, id_genre) VALUES (?, ?)");
            $stmtGenre->bind_param("ii", $lastInsertId, $genreId);
            $stmtGenre->execute();
        }
    } else {
        $errorMessage = "Gagal menambahkan game: " . $conn->error;
    }
}

// Proses jika game dihapus
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_game_id'])) {
    $deleteGameId = $_POST['delete_game_id'];
    $stmt = $conn->prepare("DELETE FROM games WHERE id_game = ?");
    $stmt->bind_param("i", $deleteGameId);
    if ($stmt->execute()) {
        $successMessage = "Game berhasil dihapus.";
    } else {
        $errorMessage = "Gagal menghapus game: " . $conn->error;
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
        $successMessage = "Game berhasil diperbarui.";
    } else {
        $errorMessage = "Gagal memperbarui game: " . $conn->error;
    }
}

// Ambil data game untuk ditampilkan
$games = $conn->query("SELECT id_game, game_name, game_desc, games_image, is_admit FROM games");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="../assets/UAP.ico" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <title>Add Game</title>
    <style>
        .navbar { background-color: #333; color: #fff; padding: 10px; }
        .navbar-brand { font-weight: bold; font-size: 1.5rem; }
        .icon-plus { font-size: 2rem; color: #007bff; }
        .card { cursor: pointer; transition: transform 0.2s; }
        .card:hover { transform: scale(1.05); }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="../main_form/mainForm.php">
                <img src="../assets/UapLogoText.svg" alt="Logo">
            </a>
        </div>
    </nav>

    <section class="container mt-5">
        <!-- Tombol Tambah Game -->
        <div class="row justify-content-left" style="width:16rem;height:17rem;">
            <div class="card text-center" id="gameCard" data-bs-toggle="modal" data-bs-target="#gameModal">
                <div class="card-body d-flex justify-content-center align-items-center">
                    <i class="bi bi-plus-circle icon-plus"></i>
                </div>
            </div>
        </div>

        <!-- Modal untuk tambah game -->
        <div class="modal fade" id="gameModal" tabindex="-1" aria-labelledby="gameModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="gameModalLabel">Tambah Game</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="" method="POST" enctype="multipart/form-data">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="coverImage" class="form-label">Gambar Cover</label>
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
                                    while ($row = $result->fetch_assoc()) {
                                        echo "<div class='form-check'>";
                                        echo "<input class='form-check-input' type='checkbox' name='gameGenres[]' value='" . $row['id_genre'] . "' id='genre" . $row['id_genre'] . "'>";
                                        echo "<label class='form-check-label' for='genre" . $row['id_genre'] . "'>" . $row['genre_name'] . "</label>";
                                        echo "</div>";
                                    }
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
        <h3 class="mt-4">Daftar Game:</h3>
        <div class="row">
            <?php
            while ($game = $games->fetch_assoc()) {
                echo "<div class='col-md-4 mb-4'>";
                echo "<div class='card h-100'>";
                echo "<img src='" . $game['games_image'] . "' alt='Cover' class='card-img-top' style='height: 200px; object-fit: cover;'>";
                echo "<div class='card-body'>";
                echo "<h5 class='card-title'>" . $game['game_name'] . "</h5>";
                echo "<p class='card-text'>" . $game['game_desc'] . "</p>";
                
                // Indikator Status
                $statusClass = $game['is_admit'] ? 'text-success' : 'text-danger'; // Menggunakan warna hijau untuk approved dan merah untuk rejected
                $statusText = $game['is_admit'] ? 'Sudah Diterima' : 'Belum Diterima';
                echo "<p class='$statusClass'>$statusText</p>";

                echo "<form method='POST'>";
                echo "<button type='submit' name='delete_game_id' value='" . $game['id_game'] . "' class='btn btn-danger btn-sm'>Hapus</button>";
                echo "</form>";
                echo "</div></div></div>";
            }
            ?>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>