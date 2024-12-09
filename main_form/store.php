<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('../db_connect/DatabaseConnection.php');

$is_logged_in = isset($_SESSION['username']) && !empty($_SESSION['username']); //set is logged in

if($is_logged_in){ //jika ada is logged_in jika ga ada username kosong
    $username = $_SESSION['username'];
}else{
    $username = '';
}

$is_publisher = false;

if ($is_logged_in) {
    // Siapkan query
    $query = "SELECT publisher_name FROM publisher WHERE publisher_name = ?";
    $stmt = $conn->prepare($query);

    if ($stmt) {
       $stmt->bind_param("s", $username);
        $stmt->execute();
    
        $result = $stmt->get_result();
        $publisher = $result->fetch_assoc(); //untuk di cek di isset($publisher['publisher_name'])

        //jika isset mengeluarkan hasil null maka is publisher akan jadi false
        if (isset($publisher['publisher_name']) && $publisher['publisher_name'] === $username) {
            $is_publisher = true;
        }else{
            $is_publisher = false;
        }
    }
}

// Search filter
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Query semua game
$query = "SELECT * FROM games";
if (!empty($search)) {
    $query .= " WHERE game_name LIKE ?";
}
$stmt = $conn->prepare($query);

if (!empty($search)) {
    $searchParam = "%" . $search . "%";
    $stmt->bind_param("s", $searchParam);
}
$stmt->execute();
$result = $stmt->get_result();


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
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .card-game {
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .card-game img {
            height: 200px;
            object-fit: cover;
        }
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
        .navbar-toggler {
            border-color: #FFFFFF; /* Tanda toggle putih */
        }
        .login-btn {
            background-color: #000000; /* Tombol hitam */
            border: 2px solid #FF4C4C; /* Garis tepi merah */
            padding: 5px 10px;
            border-radius: 3px;
            color: #FFFFFF; /* Font putih */
            text-decoration: none;
        }
        .login-btn:hover {
            background-color: #FF4C4C; /* Tombol berubah merah terang saat hover */
            color: #FFFFFF; /* Font tetap putih */
        }
        body{
            background-image: url('../assets/Backgrounds.png');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center top;
            height: 100vh;
            align-items: center; /* Agar teks di tengah secara vertikal */
            justify-content: center; /* Agar teks di tengah secara horizontal */
            color: #FFFFFF;
        }
        .LoginText {
            font-size: 50px;
            font-family: Arial, sans-serif;
            text-shadow: 2px 2px 8px rgba(0, 0, 0, 0.8); /* Memastikan teks tetap jelas */
        }
        .dropdown-toggle::after {
        display: none;
        }
        .dropdown{
            padding-right: 5rem;
        }
        .dropdown-item{
            color: white;
        }
        .dropdown-divider{
            border-color:white;
        }

        .navbar-custom{
            background: linear-gradient( rgba(200, 14, 49, 0.8), rgba(125, 7, 23, 0.8));
        }

        .navbar-custom a:hover{
            background-color: #c51d3a; /* Darker red background on hover */
            border-radius: 4px; /* Optional: Rounded corners on hover */
            color: white !important;
        }

        .search-bar {
            width: 50%;
            margin: 20px auto;
        }
        .search-bar input {
            border-radius: 25px;
            padding: 10px 20px;
            border: 1px solid #ccc;
            width: calc(100% - 50px);
            display: inline-block;
        }
        .search-bar button {
            background-color: #c51d3a;
            color: white;
            border: none;
            border-radius: 25px;
            padding: 10px 20px;
            cursor: pointer;
        }
        .search-bar button:hover {
            background-color: #a4162a;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand logo" href="mainForm.php" >
                    <img src="..\assets\Logo.svg" alt="UapLogo">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarScroll" aria-controls="navbarScroll" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-center navbar-abc" id="navbarScroll">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="store.php">Store</a>
                    </li>
                    <?php if (!$is_publisher): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="../main_form/library.php">Library</a>
                        </li>
                    <?php elseif ($is_publisher): ?>
                        <li class="nav-item">
                            <a href="../main_form/addGame.php" class="nav-link">Add Game</a>
                        </li>
                    <?php endif; ?>  
                    <li class="nav-item">
                        <a class="nav-link" href="#">Community</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link disabled" aria-disabled="true" href="#"><?php echo $username; ?></a>
                    </li>   
                </ul>
                <?php if ($is_logged_in): ?>
                    <div class="dropdown" style="background-color: #2C2C2C;">
                        <button class=" btn btn-secondary dropdown-toggle bi bi-person-circle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" style="font-size: 1.3rem; background-color: #2C2C2C;" aria-expanded="false"><?php echo " ",$username; ?></button>
                        <ul class="dropdown-menu bg-dark" aria-labelledby="dropdownMenuButton1">
                            <li><a class="dropdown-item" href="userProfile.php">Profile</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../auth/logout.php">Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <!-- Tampilkan tombol Login jika user belum login -->
                    <a href="..\auth\login.php" class="login-btn">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Search Bar -->
    <div class="search-bar text-center">
        <form method="GET" action="">
            <input type="text" name="search" placeholder="Cari game..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Search</button>
        </form>
    </div>

    <!-- Game Cards -->
    <div class="container">
        <div class="row">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                        <div class="card card-game text-bg-dark">
                            <img src="<?php echo htmlspecialchars($row['games_image']); ?>" alt="<?php echo htmlspecialchars($row['game_name']); ?>" class="card-img-top">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($row['game_name']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars(substr($row['game_desc'], 0, 50)) . '...'; ?></p>
                                <a href="gameDetail.php?game_id=<?php echo $row['id_game']; ?>"  class="btn btn-primary">View Details</a>
                                <a href="saveGame.php?game_id=<?php echo $row['id_game']; ?>" class="btn btn-success">Save Game</a>

                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <p class="text-center">Tidak ada game yang ditemukan.</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>