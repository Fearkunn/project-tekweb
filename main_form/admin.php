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
    <title>Admin Dashboard</title>
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
    .card {
            background: linear-gradient(135deg, #2c2c2c, #1a1a1a);
            color: white;
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 15px rgba(0, 0, 0, 0.5);
        }

        .card-title {
            font-weight: bold;
            font-size: 1.25rem;
        }

        /* Tabel */
        .table {
            border-radius: 10px;
            overflow: hidden;
        }

        h2, h3 {
            text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.8);
        }
        .content-box {
            background-color: rgba(0, 0, 0, 0.8); /* Warna semi-transparan */
            padding: 30px 25px; /* Ruang dalam */
            border-radius: 10px; /* Sudut membulat */
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3); /* Efek bayangan */
            margin-bottom: 20px; /* Spasi antar box */
        }
    </style>
</head>

<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand logo" href="#">
                <img src="..\assets\Logo.svg" alt="UapLogo">
            </a>
            <div class="collapse navbar-collapse justify-content-center navbar-abc" id="navbarScroll">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="admin.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="admin_approve_games.php">Approve Games</a>
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
        <h2 class="text-center mb-4">Admin Dashboard</h2>

        <!-- Statistik -->
        <div class="content-box">
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
        </div>

        <!-- Publisher Teratas -->
        <div class="content-box mt-4">
            <h3 class="pb-4">3 Publisher dengan Game Terbanyak</h3>
            <table class="table table-hover table-secondary">
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
        </div>

        <!-- Game Terpopuler -->
        <div class="content-box mt-4">
            <h3 class="pb-4">3 Game dengan Jumlah Like Terbanyak</h3>
            <table class="table table-hover table-secondary">
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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>