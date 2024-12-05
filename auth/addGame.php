<?php
// Mulai session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('../db_connect/DatabaseConnection.php');

// Proses jika form ditambahkan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['gameName'])) {
    $gameName = $_POST['gameName'];
    $gameDesc = $_POST['gameDesc'];
    $gameGenres = $_POST['gameGenres'];
    
    // Ambil id_publisher berdasarkan user_id dari session
    $userId = $_SESSION['username'];
    $stmt = $conn->prepare("SELECT id_publisher FROM publisher WHERE publisher_name = ?");
    $stmt->bind_param("i", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $idPublisher = $row['id_publisher'];
        echo "Username dari sesi: " . $idPublisher . "<br>";
    } else {
        die("Publisher tidak ditemukan untuk pengguna ini.");
    }

    // Proses upload gambar ke folder
    $coverImagePath = null;
    if (isset($_FILES['coverImage']) && $_FILES['coverImage']['error'] == 0) {
        // Tentukan path tempat gambar akan disimpan
        $uploadDir = '../uploads/';
        $fileName = basename($_FILES['coverImage']['name']);
        $coverImagePath = $uploadDir . $fileName;

        // Pindahkan file gambar ke folder tujuan
        if (!move_uploaded_file($_FILES['coverImage']['tmp_name'], $coverImagePath)) {
            die("Gagal mengupload gambar.");
        }
    }

    // Ambil nilai id_game terbesar yang ada di tabel games
    $result = $conn->query("SELECT MAX(id_game) AS max_id FROM games");
    $row = $result->fetch_assoc();
    $maxId = $row['max_id'];
    $newGameId = $maxId + 1;

    // Simpan game ke database dengan id_game yang baru
    $stmt = $conn->prepare("INSERT INTO games (id_game, game_name, game_desc, is_admit, release_date, id_publisher, games_profile) VALUES (?, ?, ?, ?, NOW(), ?, ?)");
    $isAdmit = false; 
    
    $stmt->bind_param("issiis", $newGameId, $gameName, $gameDesc, $isAdmit, $idPublisher, $coverImagePath);
    if ($stmt->execute()) {
        // Simpan genre
        if ($gameGenres) {
            foreach ($gameGenres as $genreId) {
                $stmt = $conn->prepare("INSERT INTO detail_genre (id_game, id_genre) VALUES (?, ?)");
                $stmt->bind_param("ii", $newGameId, $genreId);
                $stmt->execute();
            }
        }
        $successMessage = "Game berhasil ditambahkan.";
    } else {
        $errorMessage = "Gagal menyimpan game: " . $conn->error;
    }
}

// Ambil data game untuk ditampilkan
$games = $conn->query("SELECT game_name, game_desc, games_profile FROM games");
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
        <ul class="list-group">
            <?php
            while ($game = $games->fetch_assoc()) {
                echo "<li class='list-group-item'>";
                echo "<img src='" . $game['games_profile'] . "' alt='Cover' class='img-thumbnail me-2' style='width: 100px;'>";
                echo "<strong>" . $game['game_name'] . "</strong>";
                echo "<p>" . $game['game_desc'] . "</p>";
                echo "</li>";
            }
            ?>
        </ul>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>