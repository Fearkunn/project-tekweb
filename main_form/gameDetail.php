<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('../db_connect/DatabaseConnection.php');

// Get the game ID from the URL
$game_id = isset($_GET['game_id']) ? intval($_GET['game_id']) : 0;

// Fetch game details along with publisher, genres, and reviews
$query_game = "SELECT g.game_name, g.game_desc, g.release_date, g.like_count, g.games_image, p.publisher_name, p.publisher_logo 
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
    header("Location: store.php");
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
$query_reviews = "SELECT r.id_review, r.review_content, u.username 
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
$is_logged_in = isset($_SESSION['username']) && !empty($_SESSION['username']); //set is logged in

if($is_logged_in){ //jika ada is logged_in jika ga ada username kosong
    $username = $_SESSION['username'];
}else{
    $username = '';
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_content'])) {
    // Handle review submission
    $review_content = trim($_POST['review_content']);
    $user_id = $_SESSION['user_id']; // Assuming id_user is stored in the session
    if ($is_logged_in && !empty($review_content)) {
        $query_insert_review = "INSERT INTO review (review_content, id_user, id_game) VALUES (?, ?, ?)";
        $stmt_insert_review = $conn->prepare($query_insert_review);
        $stmt_insert_review->bind_param("sii", $review_content, $user_id, $game_id);
        $stmt_insert_review->execute();
        header("Location: gameDetail.php?game_id=$game_id"); // Refresh to display the new review
        exit();
    }
}
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_review_id'])) {
    // Validasi pengguna dan hapus ulasan
    $review_id = intval($_POST['delete_review_id']);
    $user_id = $_SESSION['user_id']; // ID pengguna dari sesi

    // Periksa apakah ulasan milik pengguna
    $query_check_review = "SELECT id_review FROM review WHERE id_review = ? AND id_user = ?";
    $stmt_check_review = $conn->prepare($query_check_review);
    $stmt_check_review->bind_param("ii", $review_id, $user_id);
    $stmt_check_review->execute();
    $result_check = $stmt_check_review->get_result();

    if ($result_check->num_rows > 0) {
        // Hapus ulasan
        $query_delete_review = "DELETE FROM review WHERE id_review = ?";
        $stmt_delete_review = $conn->prepare($query_delete_review);
        $stmt_delete_review->bind_param("i", $review_id);
        $stmt_delete_review->execute();
        header("Location: gameDetail.php?game_id=$game_id"); // Refresh halaman
        exit();
    } else {
        echo "<script>alert('You can only delete your own reviews.');</script>";
    }
}

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

    .game-container {
            max-width: 1200px;
            margin: 60px auto;
            padding: 20px;
            background-color: #333;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }

        .hero-section {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
        }

        .hero-image {
            flex: 2;
        }

        .hero-image img {
            width: 100%; /* Gambar tetap responsif */
            min-width: 300px; /* Lebar minimum */
            min-height: 200px; /* Tinggi minimum */
            max-width: 600px; /* Lebar maksimum (opsional) */
            max-height: 400px; /* Tinggi maksimum (opsional) */
            object-fit: cover; /* Gambar akan menyesuaikan tanpa distorsi */
            border-radius: 10px; /* Sudut gambar melengkung */
        }

        .game-details {
            flex: 3;
            padding: 20px;
            border-radius: 10px;
        }

        .game-title {
            color: #ffffff;
            font-size: 2rem;
            margin-bottom: 15px;
        }

        .game-description {
            margin-top: 15px;
        }

        .tags {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .tag {
            background-color: #4c6b8a;
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 0.9rem;
            color: white;
        }

        .actions {
            display: flex;
            gap: 15px;
        }

        .btn-custom {
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 1rem;
            border: none;
            cursor: pointer;
        }
        .reviews-section {
            margin-top: 30px;
            padding: 20px;
            border-radius: 10px;
        }

        .review {
            margin-bottom: 20px;
            padding: 15px;
            background-color: #34495e;
            border-radius: 5px;
        }

        .review strong {
            color: #ffffff;
        }
    </style>
</head>
<body>

<nav class="navbar navbar-expand-lg">
    <div class="container-fluid">
        <a class="navbar-brand logo" href="mainForm.php">
            <img src="..\assets\Logo.svg" alt="UapLogo">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarScroll" aria-controls="navbarScroll" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse justify-content-center navbar-abc" id="navbarScroll">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link active" aria-current="page" href="store.php">Store</a></li>
                <li class="nav-item"><a class="nav-link" href="../main_form/library.php">Library</a></li>
                <li class="nav-item"><a class="nav-link" href="#">Community</a></li>
                <li class="nav-item"><a class="nav-link disabled" aria-disabled="true" href="#"><?php echo $username; ?></a></li>
            </ul>
            <?php if ($is_logged_in): ?>
                <div class="dropdown" style="background-color: #2C2C2C;">
                    <button class=" btn btn-secondary dropdown-toggle bi bi-person-circle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" style="font-size: 1.3rem; background-color: #2C2C2C;" aria-expanded="false">
                        <?php echo " ", $username; ?>
                    </button>
                    <ul class="dropdown-menu bg-dark" aria-labelledby="dropdownMenuButton1">
                        <li><a class="dropdown-item" href="userProfile.php">Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="../auth/logout.php">Logout</a></li>
                    </ul>
                </div>
            <?php else: ?>
                <a href="..\auth\login.php" class="btn btn-danger">Login</a>
            <?php endif; ?>
        </div>
    </div>
</nav>

<div class="container game-container">
    <div class="hero-section">
        <div class="hero-image">
            <img src="<?php echo htmlspecialchars($game['games_image']); ?>" alt="<?php echo htmlspecialchars($game['game_name']); ?>">
        </div>
        <div class="game-details">
            <h1 class="game-title"><?php echo htmlspecialchars($game['game_name']); ?></h1>
            <p><strong>Publisher:</strong> <?php echo htmlspecialchars($game['publisher_name']); ?></p>
            <p><strong>Release Date:</strong> <?php echo date('F j, Y', strtotime($game['release_date'])); ?></p>
            <p class="game-description"><?php echo nl2br(htmlspecialchars($game['game_desc'])); ?></p>
            <p><strong>Genres:</strong></p>
            <div class="tags">
                <?php foreach ($genres as $genre): ?>
                    <span class="tag"><?php echo htmlspecialchars($genre); ?></span>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="reviews-section">
    <h2>Reviews</h2>
    <?php if ($is_logged_in): ?>
        <!-- Add Review Form -->
        <button class="btn btn-primary my-3 " id="toggle-review-form">Add Review</button>
        <form method="POST" class="mt-3" id="review-form" style="display: none;">
            <textarea name="review_content" class="form-control mb-2" rows="4" placeholder="Write your review here..."></textarea>
            <button type="submit" class="btn btn-success my-2">Submit Review</button>
        </form>
        <script>
            document.getElementById('toggle-review-form').addEventListener('click', function () {
                const form = document.getElementById('review-form');
                form.style.display = form.style.display === 'none' ? 'block' : 'none';
            });
        </script>
    <?php else: ?>
        <p>You must <a href="../auth/login.php">log in</a> to add a review.</p>
    <?php endif; ?>
    <?php if (!empty($reviews)): ?>
        <?php foreach ($reviews as $review): ?>
            <div class="review d-flex justify-content-between align-items-start">
                <div class="review-content">
                    <p><strong><?php echo htmlspecialchars($review['username']); ?>:</strong></p>
                    <p><?php echo nl2br(htmlspecialchars($review['review_content'])); ?></p>
                </div>
                <?php if ($is_logged_in && $username === $review['username']): ?>
                    <!-- Tombol Hapus untuk Ulasan Milik Pengguna -->
                    <form method="POST">
                        <input type="hidden" name="delete_review_id" value="<?php echo htmlspecialchars($review['id_review']); ?>">
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p>No reviews yet.</p>
    <?php endif; ?>

    
</div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>