<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('../db_connect/DatabaseConnection.php');

// Get the game ID from the URL
$game_id = isset($_GET['game_id']) ? intval($_GET['game_id']) : 0;

// Fetch game details along with publisher, genres, and reviews
$query_game = "SELECT g.game_name, g.game_desc, g.release_date, g.like_count, g.games_profile, p.publisher_name, p.publisher_logo 
               FROM games g
               JOIN publisher p ON g.id_publisher = p.id_publisher
               WHERE g.id_game = ?";
$stmt = $conn->prepare($query_game);
$stmt->bind_param("i", $game_id);
$stmt->execute();
$result_game = $stmt->get_result();
$game = $result_game->fetch_assoc();

if (!$game) {
    // Redirect or show an error if game not found
    header("Location: ../index.php");
    exit();
}

// Fetch genres
$query_genres = "SELECT g.genre_name 
                 FROM detail_genre dg
                 JOIN genre g ON dg.id_genre = g.id_genre
                 WHERE dg.id_game = ?";
$stmt_genres = $conn->prepare($query_genres);
$stmt_genres->bind_param("i", $game_id);
$stmt_genres->execute();
$result_genres = $stmt_genres->get_result();
$genres = [];
while ($row = $result_genres->fetch_assoc()) {
    $genres[] = $row['genre_name'];
}

// Fetch reviews
$query_reviews = "SELECT r.review_content, u.username 
                  FROM review r
                  JOIN users u ON r.id_user = u.id_user
                  WHERE r.id_game = ?";
$stmt_reviews = $conn->prepare($query_reviews);
$stmt_reviews->bind_param("i", $game_id);
$stmt_reviews->execute();
$result_reviews = $stmt_reviews->get_result();
$reviews = [];
while ($row = $result_reviews->fetch_assoc()) {
    $reviews[] = $row;
}

// Check if user is logged in
$is_logged_in = isset($_SESSION['username']) && !empty($_SESSION['username']);
$username = $is_logged_in ? $_SESSION['username'] : '';

// Include cookie validation
include('../auth/cookieValidation.php');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($game['game_name']); ?> - Game Detail</title>
    <link rel="icon" href="../assets/UAP.ico" type="image/x-icon"> 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .navbar {
            background-color: #2C2C2C; /* Dark grey */
        }
        .navbar-brand, .nav-link {
            color: #FFFFFF !important; /* White font for contrast */
        }
        .game-image {
            max-width: 100%; /* Make sure image is responsive */
            border-radius: 10px;
        }
        .game-description {
            font-size: 1.1rem;
            margin-top: 15px;
        }
        .like-count {
            color: #FF4C4C; /* Bright red for likes */
            font-weight: bold;
        }
        .review {
            margin-top: 20px;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="../index.php">Game Store</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarScroll" aria-controls="navbarScroll" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarScroll">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" href="../index.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#storeSection">Store</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Library</a>
                    </li>
                    <?php if ($is_logged_in): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="#"><?php echo htmlspecialchars($username); ?></a>
                        </li>
                    <?php endif; ?>
                </ul>
                <?php if ($is_logged_in): ?>
                    <a href="../auth/logout.php" class="btn btn-danger">Logout</a>
                <?php else: ?>
                    <a href="../auth/login.php" class="btn btn-success">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <h1><?php echo htmlspecialchars($game['game_name']); ?></h1>
        <img src="<?php echo htmlspecialchars($game['games_profile']); ?>" alt="<?php echo htmlspecialchars($game['game_name']); ?>" class="game-image">
        <p class="like-count">Likes: <?php echo htmlspecialchars($game['like_count']); ?></p>
        <p class="game-description"><?php echo nl2br(htmlspecialchars($game['game_desc'])); ?></p>
        <p><strong>Publisher:</strong> <?php echo htmlspecialchars($game['publisher_name']); ?></p>
        <?php if ($game['publisher_logo']): ?>
            <img src="<?php echo htmlspecialchars($game['publisher_logo']); ?>" alt="Publisher Logo" class="game-image" style="max-width: 100px;">
        <?php endif; ?>
        <p><strong>Release Date:</strong> <?php echo date('F j, Y', strtotime($game['release_date'])); ?></p>
        <p><strong>Genres:</strong> <?php echo implode(', ', $genres); ?></p>

        <h2>Reviews</h2>
        <?php if (!empty($reviews)): ?>
            <?php foreach ($reviews as $review): ?>
                <div class="review">
                    <p><strong><?php echo htmlspecialchars($review['username']); ?>:</strong></p>
                    <p><?php echo nl2br(htmlspecialchars($review['review_content'])); ?></p>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>No reviews yet.</p>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>