<?php
// Start session and include database connection
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('../db_connect/DatabaseConnection.php');

// Check if user is logged in
$is_logged_in = isset($_SESSION['username']) && !empty($_SESSION['username']);
$user_id = $is_logged_in ? ($_SESSION['user_id'] ?? '') : '';

// Redirect to login page if not logged in
if (!$is_logged_in) {
    header("Location: ../auth/login.php");
    exit;
}
if($is_logged_in){ //jika ada is logged_in jika ga ada username kosong
    $username = $_SESSION['username'];
}else{
    $username = '';
}

// Fetch user's games from the database
$order_by = isset($_GET['sort']) && $_GET['sort'] === 'asc' ? 'ASC' : 'DESC'; // Default sorting
$query = "
    SELECT l.id_library, g.game_name, g.games_image, g.like_count
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
$userLiked = '';

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
        .navbar-custom{
        background: linear-gradient( rgba(200, 14, 49, 0.8), rgba(125, 7, 23, 0.8));
        }
        .navbar-custom a:hover{
            background-color: #c51d3a; /* Darker red background on hover */
            border-radius: 4px; /* Optional: Rounded corners on hover */
            color: white !important;
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
        .game-card {
            border: none;
            border-radius: 10px;
            overflow: hidden;
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
                    <li class="nav-item">
                       <a class="nav-link" href="../main_form/library.php">Library</a>
                    </li>  
                    <li class="nav-item">
                        <a class="nav-link" href="#">Community</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link disabled" aria-disabled="true" href="#"><?php echo $username; ?></a>
                    </li>   
                </ul>
                <div class="dropdown" style="background-color: #2C2C2C;">
                    <button class=" btn btn-secondary dropdown-toggle bi bi-person-circle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" style="font-size: 1.3rem; background-color: #2C2C2C;" aria-expanded="false"><?php echo " ",$username; ?></button>
                    <ul class="dropdown-menu bg-dark" aria-labelledby="dropdownMenuButton1">
                        <li><a class="dropdown-item" href="userProfile.php">Profile</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="../auth/logout.php">Logout</a></li>
                    </ul>
                </div>         
            </div>
        </div>
    </nav>

    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center">
            <h1>Your Library</h1>
            <a href="?sort=asc" class="btn btn-secondary sort-button">Sort Ascending</a>
        </div>
        
        <div class="row py-5">
            <?php if (!empty($library)): ?>
                <?php foreach ($library as $game): ?>
                    <div class="col-md-3 mb-4">
                        <div class="card text-bg-dark game-card">
                            <img src="<?php echo htmlspecialchars($game['games_image']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($game['game_name']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($game['game_name']); ?></h5>
                                <p class="card-text">Likes: <?php echo htmlspecialchars($game['like_count']); ?></p>
                                <div class="d-flex justify-content-between align-items-center">
                                    <!-- Like Checkbox -->
                                    <div>
                                        <input type="checkbox" class="form-check-input like-checkbox" data-game-id="<?php echo $game['id_library']; ?>" id="like-<?php echo $game['id_library']; ?>" 
                                            <?php echo $userLiked ? 'checked' : ''; ?>>
                                        <label for="like-<?php echo $game['id_library']; ?>">Like</label>
                                    </div>
                                    
                                    <!-- Review Button -->
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#reviewModal-<?php echo $game['id_library']; ?>">
                                        Review
                                    </button>
                                </div>
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
    <script>
            document.querySelectorAll('.like-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const gameId = this.getAttribute('data-game-id');
                const isLiked = this.checked;
                console.log(isLiked);

                fetch('likeGame.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({ game_id: gameId, liked: isLiked })
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);

                    // Update jumlah like di UI
                    const likeCountElement = this.closest('.card').querySelector('.card-text');
                    let currentLikeCount = parseInt(likeCountElement.innerText.replace('Likes: ', ''));
                    console.log(currentLikeCount);

                    if (isLiked) {
                        likeCountElement.innerText = `Likes: ${currentLikeCount + 1}`;
                    } else {
                        likeCountElement.innerText = `Likes: ${currentLikeCount - 1}`;
                    }
                });
            });
        });

    </script>
</body>
</html>