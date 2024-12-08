<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('../db_connect/DatabaseConnection.php');

// Periksa apakah user adalah admin
$is_admin = isset($_SESSION['role_user']) && $_SESSION['role_user'] === 'admin';

if (!$is_admin) {
    header('Location: ../auth/login.php'); // Redirect jika bukan admin
    exit;
}

// Ambil data semua game
$searchTerm = '';
if (isset($_POST['search'])) {
    $searchTerm = $_POST['search'];
    $query_all_games = "
        SELECT 
        g.id_game, 
        g.game_name, 
        g.game_desc,
        g.games_image,
        p.publisher_name,
        GROUP_CONCAT(gen.genre_name SEPARATOR ', ') AS genres
    FROM games g
    JOIN publisher p ON g.id_publisher = p.id_publisher
    LEFT JOIN detail_genre dg ON g.id_game = dg.id_game
    LEFT JOIN genre gen ON dg.id_genre = gen.id_genre
    WHERE g.is_admit = 1 AND g.game_name LIKE ?
    GROUP BY g.id_game 
    ";
    $stmt = $conn->prepare($query_all_games);
    $likeTerm = "%" . $searchTerm . "%";
    $stmt->bind_param("s", $likeTerm);
    $stmt->execute();
    $result_all_games = $stmt->get_result();
} else {
    // Default query to get all games
    $query_all_games = "
        SELECT 
        g.id_game, 
        g.game_name, 
        g.game_desc,
        g.games_image,
        p.publisher_name,
        GROUP_CONCAT(gen.genre_name SEPARATOR ', ') AS genres
    FROM games g
    JOIN publisher p ON g.id_publisher = p.id_publisher
    LEFT JOIN detail_genre dg ON g.id_game = dg.id_game
    LEFT JOIN genre gen ON dg.id_genre = gen.id_genre
    WHERE g.is_admit = 1
    GROUP BY g.id_game
    ";
    $result_all_games = $conn->query($query_all_games);
}

// Hapus game
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $gameId = intval($_POST['game_id']);
    $stmt = $conn->prepare("DELETE FROM detail_genre WHERE id_game = ?");
    $stmt->bind_param("i", $gameId);
    if ($stmt->execute()) {
        $successMessage = "Detail berhasil dihapus.";
    } else {
        $errorMessage = "Gagal menghapus detail: " . $conn->error;
    }
    $stmt = $conn->prepare("DELETE FROM games WHERE id_game = ?");
    $stmt->bind_param("i", $gameId);
    if ($stmt->execute()) {
        $successMessage = "Game berhasil dihapus.";
    } else {
        $errorMessage = "Gagal menghapus game: " . $conn->error;
    }
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Delete Games</title>
    <link rel="icon" href="../assets/UAP.ico" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
    .navbar {
    background-color: #2C2C2C; /* Tetap abu-abu gelap */
    font-family: Arial, sans-serif;
    }
    .navbar-brand, .nav-link {
        color: #FFFFFF !important; /* Font putih untuk kontras */
    }
    .navbar-brand {
        font-weight: bold;
        font-size: 1.25rem;
    }
    .navbar-abc .nav-link:hover {
        color: #FF4C4C !important; /* Merah terang saat hover */
    }
    .nav-link {
        margin-right: 1.5rem;
    }
    .logout-btn {
        background-color: #000000; /* Tombol hitam */
        border: 2px solid #FF4C4C; /* Garis tepi merah */
        padding: 5px 10px;
        border-radius: 3px;
        color: #FFFFFF; /* Font putih */
        text-decoration: none;
    }
    .logout-btn:hover {
        background-color: #FF4C4C; /* Tombol berubah merah terang saat hover */
        color: #FFFFFF; /* Font tetap putih */
    }
    body{
        background-image: url('../assets/Background.png');
        background-size: cover;
        background-repeat: no-repeat;
        background-position: center top;
        height: 100vh;
        align-items: center; /* Agar teks di tengah secara vertikal */
        justify-content: center; /* Agar teks di tengah secara horizontal */
        color: #FFFFFF;
    }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand logo" href="admin.php">
                <img src="..\assets\Logo.svg" alt="UapLogo">
            </a>
            <div class="collapse navbar-collapse justify-content-center navbar-abc" id="navbarScroll">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="admin.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_approve_games.php">Approve Games</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" aria-current="page" href="admin_delete_games.php">Delete Games</a>
                    </li>
                </ul>
                <a href="../auth/logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <h2 class="text-center pb-5">Delete Games</h2>

        <?php if (isset($errorMessage)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $errorMessage; ?>
            </div>
        <?php endif; ?>

        <!-- Search Form -->
        <form method="POST" class="mb-4">
            <div class="input-group">
                <input type="text" class="form-control" name="search" placeholder="Search by game name..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                <button class="btn btn-secondary" type="submit">Search</button>
            </div>
        </form>

        <h3 class="pt-3 pb-4">Daftar Game:</h3>
        <div class="row">
            <?php while ($row = $result_all_games->fetch_assoc()): ?>
                <div class="col-md-4 mb-4">
                    <div class="card text-bg-dark h-100">
                        <img src="<?php echo $row['games_image']; ?>" alt="Cover" class="card-img-top" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $row['game_name']; ?></h5>
                            <p class="card-text"><?php echo $row['game_desc']; ?></p>
                            <p class="card-text">Publisher: <?php echo $row['publisher_name']; ?></p>
                            <p class="card-text pb-3">Genre: <?php echo $row['genres']; ?></p>
                            <form method="POST">
                                <input type="hidden" name="game_id" value="<?php echo $row['id_game']; ?>">
                                <button type="submit" name="action" value="delete" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this game?');">Hapus</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>