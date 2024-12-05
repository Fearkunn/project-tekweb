<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('../db_connect/DatabaseConnection.php');

// Periksa apakah user adalah admin
$is_admin = isset($_SESSION['role_user'])&& $_SESSION['role_user']=== 'admin';

if (!$is_admin) {
    header('Location: ../auth/login.php'); // Redirect jika bukan admin
    exit;
}

// Ambil data statistik
$totalGamesQuery = "SELECT COUNT(*) AS total FROM games";
$totalGamesResult = $conn->query($totalGamesQuery);
$totalGames = $totalGamesResult->fetch_assoc()['total'];

$totalApprovedQuery = "SELECT COUNT(*) AS total FROM games WHERE is_admit = 1";
$totalApprovedResult = $conn->query($totalApprovedQuery);
$totalApproved = $totalApprovedResult->fetch_assoc()['total'];

$totalPendingQuery = "SELECT COUNT(*) AS total FROM games WHERE is_admit = 0";
$totalPendingResult = $conn->query($totalPendingQuery);
$totalPending = $totalPendingResult->fetch_assoc()['total'];

$totalUsersQuery = "SELECT COUNT(*) AS total FROM users";
$totalUsersResult = $conn->query($totalUsersQuery);
$totalUsers = $totalUsersResult->fetch_assoc()['total'];

$topPublishersQuery = "
    SELECT p.publisher_name, COUNT(g.id_game) AS game_count 
    FROM publisher p 
    JOIN games g ON p.id_publisher = g.id_publisher 
    GROUP BY p.publisher_name 
    ORDER BY game_count DESC 
    LIMIT 3
";
$topPublishersResult = $conn->query($topPublishersQuery);
$topPublishers = [];
while ($row = $topPublishersResult->fetch_assoc()) {
    $topPublishers[] = $row;
}

$topLikedGamesQuery = "
    SELECT g.game_name, g.like_count 
    FROM games g 
    ORDER BY g.like_count DESC 
    LIMIT 3
";
$topLikedGamesResult = $conn->query($topLikedGamesQuery);
$topLikedGames = [];
while ($row = $topLikedGamesResult->fetch_assoc()) {
    $topLikedGames[] = $row;
}
?>
<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin - Dashboard</title>
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
                        <a class="nav-link active" href="admin_dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_approve_games.php">Approve Games</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_delete_games.php">Delete Games</a>
                    </li>
                </ul>
                <a href="../auth/logout.php" class="btn btn-secondary">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <h2 class="text-center mb-4">Dashboard Admin</h2>

        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Game</h5>
                        <p class="card-text"><?php echo $totalGames; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Game Disetujui</h5>
                        <p class="card-text"><?php echo $totalApproved; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Game Belum Disetujui</h5>
                        <p class="card-text"><?php echo $totalPending; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Pengguna</h5>
                        <p class="card-text"><?php echo $totalUsers; ?></p>
                    </div>
                </div>
            </div>
        </div>

        <h3 class="mt-4">3 Publisher Teratas dengan Game Terbanyak</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Publisher</th>
                    <th>Jumlah Game</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($topPublishers as $publisher): ?>
                    <tr>
                        <td><?php echo $publisher['publisher_name']; ?></td>
                        <td><?php echo $publisher['game_count']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <h3 class="mt-4">3 Game dengan Like Count Terbanyak</h3>
        <table class="table">
            <thead>
                <tr>
                    <th>Game</th>
                    <th>Jumlah Like</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($topLikedGames as $game): ?>
                    <tr>
                        <td><?php echo $game['game_name']; ?></td>
                        <td><?php echo $game['like_count']; ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>