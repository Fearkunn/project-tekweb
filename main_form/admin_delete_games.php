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
            p.publisher_name 
        FROM games g
        JOIN publisher p ON g.id_publisher = p.id_publisher
        WHERE g.is_admit = 1 AND g.game_name LIKE ?
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
            p.publisher_name 
        FROM games g
        JOIN publisher p ON g.id_publisher = p.id_publisher
        WHERE g.is_admit = 1;
    ";
    $result_all_games = $conn->query($query_all_games);
}

// Hapus game
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $gameId = intval($_POST['game_id']);
    $deleteQuery = "DELETE FROM games WHERE id_game = ?";
    $deleteStmt = $conn->prepare($deleteQuery);
    $deleteStmt->bind_param("i", $gameId);
    if ($deleteStmt->execute()) {
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
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
</head>

<body>
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #2C2C2C;">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Admin Dashboard</a>
            <div class="collapse navbar-collapse">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="admin.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_approve_games.php">Approve Games</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="admin_delete_games.php">Delete Games</a>
                    </li>
                </ul>
                <a href="../auth/logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <h2 class="text-center mb-4">Delete Games</h2>

        <?php if (isset($errorMessage)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo $errorMessage; ?>
            </div>
        <?php endif; ?>

        <!-- Search Form -->
        <form method="POST" class="mb-4">
            <div class="input-group">
                <input type="text" class="form-control" name="search" placeholder="Search by game name..." value="<?php echo htmlspecialchars($searchTerm); ?>">
                <button class="btn btn-primary" type="submit">Search</button>
            </div>
        </form>

        <h3 class="mt-4">Daftar Game:</h3>
        <div class="row">
            <?php while ($row = $result_all_games->fetch_assoc()): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100">
                        <img src="<?php echo $row['games_image']; ?>" alt="Cover" class="card-img-top" style="height: 200px; object-fit: cover;">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $row['game_name']; ?></h5>
                            <p class="card-text"><?php echo $row['game_desc']; ?></p>
                            <p class="text-muted">Publisher: <?php echo $row['publisher_name']; ?></p>
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