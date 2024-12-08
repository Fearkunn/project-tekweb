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

// Ambil data game yang belum disetujui
$query_pending = "
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
    WHERE g.is_admit = 0
    GROUP BY g.id_game
";
$result_pending = $conn->query($query_pending);

// Proses approve game
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'approve') {
    $gameId = intval($_POST['game_id']);
    $updateQuery = "UPDATE games SET is_admit = 1 WHERE id_game = ?";
    $updateStmt = $conn->prepare($updateQuery);
    $updateStmt->bind_param("i", $gameId);
    if ($updateStmt->execute()) {
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $errorMessage = "Gagal menyetujui game: " . $conn->error;
    }
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Approve Games</title>
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
                        <a class="nav-link" aria-current="page" href="admin_approve_games.php">Approve Games</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_delete_games.php">Delete Games</a>
                    </li>
                </ul>
                <a href="../auth/logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <h2 class="text-center mb-4">Approve Games</h2>

        <?php if (isset($errorMessage)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $errorMessage; ?>
            </div>
        <?php endif; ?>

        <h3 class="py-5">Daftar Game:</h3>
        <div class="row">
            <?php while ($row = $result_pending->fetch_assoc()): ?>
                <div class="col-md-4 mb-4">
                    <div class="card text-bg-dark h-100">
                        <img src="<?php echo $row['games_image']; ?>" alt="Cover" class="card-img-top" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $row['game_name']; ?></h5>
                            <p class="card-text"><?php echo $row['game_desc']; ?></p>
                            <p class="card-text">Publisher: <?php echo $row['publisher_name']; ?></p>
                            <p class="card-text">Genre: <?php echo $row['genres']; ?></p>
                            <form method="POST">
                                <input type="hidden" name="game_id" value="<?php echo $row['id_game']; ?>">
                                <button type="submit" name="action" value="approve" class="btn btn-success">Approve</button>
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