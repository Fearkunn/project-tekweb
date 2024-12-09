<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('../db_connect/DatabaseConnection.php');

// Periksa login
$is_logged_in = isset($_SESSION['username']) && !empty($_SESSION['username']);
$username = $is_logged_in ? $_SESSION['username'] : '';

// Periksa apakah user adalah publisher
$is_publisher = false;
if ($is_logged_in) {
    $query = "SELECT id_publisher FROM publisher WHERE publisher_name = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();
    $is_publisher = $stmt->num_rows > 0;
}

// Genre dan Publisher Filter
$selectedGenre = isset($_GET['genre']) ? $_GET['genre'] : '';
$selectedPublisher = isset($_GET['publisher']) ? $_GET['publisher'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Ambil data genre
$genresQuery = "SELECT id_genre, genre_name FROM genre";
$genresResult = $conn->query($genresQuery);

// Ambil data publisher
$publishersQuery = "SELECT id_publisher, publisher_name FROM publisher";
$publishersResult = $conn->query($publishersQuery);

// Query untuk games dengan filter
$query = "
    SELECT g.*, 
           GROUP_CONCAT(DISTINCT gen.genre_name SEPARATOR ', ') AS genres, 
           p.publisher_name
    FROM games g
    LEFT JOIN detail_genre dg ON g.id_game = dg.id_game
    LEFT JOIN genre gen ON dg.id_genre = gen.id_genre
    LEFT JOIN publisher p ON g.id_publisher = p.id_publisher
    WHERE 1=1
";

$params = [];
$types = "";

// Filter genre
if (!empty($selectedGenre)) {
    $query .= " AND dg.id_genre = ?";
    $params[] = $selectedGenre;
    $types .= "i";
}

// Filter publisher
if (!empty($selectedPublisher)) {
    $query .= " AND g.id_publisher = ?";
    $params[] = $selectedPublisher;
    $types .= "i";
}

// Filter search
if (!empty($search)) {
    $query .= " AND g.game_name LIKE ?";
    $params[] = "%" . $search . "%";
    $types .= "s";
}

$query .= " GROUP BY g.id_game";

$stmt = $conn->prepare($query);

if ($types) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();

// Ambil game di library
$libraryGames = [];
if ($is_logged_in) {
    $libraryQuery = "SELECT id_game FROM library WHERE id_user = (SELECT id_user FROM users WHERE username = ?)";
    $libraryStmt = $conn->prepare($libraryQuery);
    $libraryStmt->bind_param("s", $username);
    $libraryStmt->execute();
    $libraryResult = $libraryStmt->get_result();
    while ($libRow = $libraryResult->fetch_assoc()) {
        $libraryGames[] = $libRow['id_game'];
    }
}
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

    <!-- Filter Genre dan Publisher -->
<div class="container my-3">
    <form method="GET" action="" class="row">
        <div class="col-md-4">
            <select name="genre" class="form-select">
                <option value="">All Genres</option>
                <?php while ($row = $genresResult->fetch_assoc()): ?>
                    <option value="<?php echo $row['id_genre']; ?>" <?php echo $selectedGenre == $row['id_genre'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($row['genre_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-4">
            <select name="publisher" class="form-select">
                <option value="">All Publishers</option>
                <?php while ($row = $publishersResult->fetch_assoc()): ?>
                    <option value="<?php echo $row['id_publisher']; ?>" <?php echo $selectedPublisher == $row['id_publisher'] ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($row['publisher_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary w-100">Filter</button>
        </div>
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
                            <p class="card-text"><small>Genres: <?php echo htmlspecialchars($row['genres']); ?></small></p>
                            <p class="card-text"><small>Publisher: <?php echo htmlspecialchars($row['publisher_name']); ?></small></p>
                            <a href="gameDetail.php?game_id=<?php echo $row['id_game']; ?>" class="btn btn-primary">View Details</a>
                            <?php if (!in_array($row['id_game'], $libraryGames)): ?>
                                <a href="saveGame.php?game_id=<?php echo $row['id_game']; ?>" class="btn btn-success">Save Game</a>
                            <?php endif; ?>
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