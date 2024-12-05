<?php
// Start session and include database connection
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('../db_connect/DatabaseConnection.php');

// Check if user is logged in
$is_logged_in = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
$user_id = $is_logged_in ? $_SESSION['user_id'] : '';

// Redirect to login page if not logged in
if (!$is_logged_in) {
    header("Location: ../auth/login.php");
    exit;
}

// Fetch user's games from the database
$order_by = isset($_GET['sort']) && $_GET['sort'] === 'asc' ? 'ASC' : 'DESC'; // Default sorting
$query = "
    SELECT l.id_library, g.game_name, g.games_profile
    FROM library l
    INNER JOIN games g ON l.id_game = g.id_game
    WHERE l.id_user = ?
    ORDER BY g.game_name $order_by
";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$library = $result->fetch_all(MYSQLI_ASSOC);

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Library</title>
    <link rel="icon" href="../assets/UAP.ico" type="image/x-icon">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        body {
            background-image: url('../assets/Background.png');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center top;
            color: #FFFFFF;
        }
        .navbar {
            background-color: #2C2C2C;
            font-family: Arial, sans-serif;
        }
        .navbar-brand, .nav-link {
            color: #FFFFFF !important;
        }
        .navbar-abc .nav-link:hover {
            color: #FF4C4C !important;
        }
        .game-card {
            background-color: rgba(0, 0, 0, 0.7);
            border: none;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
            color: #FFFFFF;
        }
        .game-card img {
            height: 200px;
            object-fit: cover;
        }
        .sort-button {
            margin-top: 20px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand logo" href="mainForm.php">
                <img src="../assets/Logo.svg" alt="UapLogo">
            </a>
            <div class="collapse navbar-collapse justify-content-center navbar-abc" id="navbarScroll">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="../main_form/mainForm.php">Store</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="#">Library</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Community</a>
                    </li>
                </ul>
                <a href="../auth/logout.php" class="btn btn-danger">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center">
            <h1>Your Library</h1>
            <a href="?sort=asc" class="btn btn-secondary sort-button">Sort Ascending</a>
        </div>
        
        <div class="row">
            <?php if (!empty($library)): ?>
                <?php foreach ($library as $game): ?>
                    <div class="col-md-3 mb-4">
                        <div class="card game-card">
                            <img src="<?php echo htmlspecialchars($game['games_profile']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($game['game_name']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($game['game_name']); ?></h5>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12">
                    <p class="text-center">You don't own any games yet. Visit the store to purchase games!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>