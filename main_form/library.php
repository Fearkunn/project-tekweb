<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('../db_connect/DatabaseConnection.php');

// Check jika user sudah login
$is_logged_in = isset($_SESSION['username']) && !empty($_SESSION['username']);
$user_id = $is_logged_in ? ($_SESSION['user_id'] ?? '') : '';

// Redirect ke login page jika belum login
if (!$is_logged_in) {
    header("Location: ../auth/login.php");
    exit;
}
if($is_logged_in){ //jika ada is logged_in jika ga ada username kosong
    $username = $_SESSION['username'];
}else{
    $username = '';
}

// Handle Like/Unlike action
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['like_game_id'])) {
    $likedGameId = intval($_POST['like_game_id']);
    $isLiked = isset($_POST['liked']) ? 1 : 0;

    // Update is_like di tabel library
    $updateLikeQuery = "UPDATE library SET is_like = ? WHERE id_user = ? AND id_game = ?";
    $stmt = $conn->prepare($updateLikeQuery);
    $stmt->bind_param("iii", $isLiked, $user_id, $likedGameId);
    $stmt->execute();

    // Update like count di tabel games
    if ($isLiked) {
        $incrementQuery = "UPDATE games SET like_count = like_count + 1 WHERE id_game = ?";
        $stmt = $conn->prepare($incrementQuery);
        $stmt->bind_param("i", $likedGameId);
        $stmt->execute();
    } else {
        $decrementQuery = "UPDATE games SET like_count = like_count - 1 WHERE id_game = ?";
        $stmt = $conn->prepare($decrementQuery);
        $stmt->bind_param("i", $likedGameId);
        $stmt->execute();
    }
    header("Location: " . $_SERVER['PHP_SELF']);
    exit;
}

// Fetch games user  dari database
$order_by = '';
if (isset($_GET['sort'])) {
    if ($_GET['sort'] === 'asc') {
        $order_by = 'ASC';
    } elseif ($_GET['sort'] === 'desc') {
        $order_by = 'DESC';
    }
}

$query = "
    SELECT l.id_library, g.id_game, g.game_name, g.games_image, g.like_count, l.is_like
    FROM library l
    INNER JOIN games g ON l.id_game = g.id_game
    WHERE l.id_user = ?
" . ($order_by ? " ORDER BY g.game_name $order_by" : '');

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
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.1/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .navbar {
        background-color: #2C2C2C; /* Tetap abu-abu gelap */
        font-family: Arial, sans-serif;
        }
        .navbar-brand, .nav-link {
            color: #FFFFFF !important; /* Font putih untuk kontras */
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
            <a class="navbar-brand logo" href="mainForm.php">
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
                        <a class="nav-link disabled" aria-disabled="true" href="#"><?php echo $username; ?></a>
                    </li>
                </ul>
                <div class="dropdown" style="background-color: #2C2C2C;">
                    <button class="btn btn-secondary dropdown-toggle bi bi-person-circle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" style="font-size: 1.3rem; background-color: #2C2C2C;" aria-expanded="false"><?php echo " ",$username; ?></button>
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
            <!-- Toggle between Ascending, Descending and No Sorting -->
            <a href="?sort=<?php echo $order_by === 'ASC' ? 'desc' : ($order_by === 'DESC' ? '' : 'asc'); ?>" class="btn btn-secondary sort-button">
                <?php 
                    if ($order_by === 'ASC') {
                        echo 'Sort Descending';
                    } elseif ($order_by === 'DESC') {
                        echo 'Order By History';
                    } else {
                        echo 'Sort Ascending';
                    }
                ?>
            </a>
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
                                    <form method="POST" action="" id="like-form-<?php echo $game['id_game']; ?>">
                                        <input type="hidden" name="like_game_id" value="<?php echo $game['id_game']; ?>">
                                        <input type="checkbox" class="form-check-input like-checkbox" 
                                            name="liked" 
                                            id="like-<?php echo $game['id_game']; ?>" 
                                            <?php echo $game['is_like'] == 1 ? 'checked' : ''; ?>>
                                        <label for="like-<?php echo $game['id_game']; ?>">Like</label>
                                    </form>
                                    <a href="gameDetail.php?game_id=<?php echo $game['id_game']; ?>" class="btn btn-info">Review</a>
                                </div>
                                <div class="mt-3">
                                    <?php if (!empty($game['games_image'])): ?>
                                        <a href="downloadgame.php?game_id=<?php echo $game['id_game']; ?>" class="btn btn-success" onclick="showDownloadSuccessAlert(event)">Download Game</a>
                                    <?php else: ?>
                                        <span class="text-warning">Download unavailable</span>
                                    <?php endif; ?>
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

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Ambil semua checkbox dengan class `like-checkbox`
            const likeCheckboxes = document.querySelectorAll('.like-checkbox');
            
            likeCheckboxes.forEach(function (checkbox) {
                checkbox.addEventListener('change', function () {
                    const form = this.closest('form');
                    const isChecked = this.checked;

                    // SweetAlert untuk status like
                    if (isChecked) {
                        Swal.fire({
                            title: 'Like Diberikan!',
                            text: 'anda memberikan like untuk game ini',
                            icon: 'success',
                        }).then(() => {
                            form.submit();
                        });
                    } else {
                        Swal.fire({
                            title: 'Like Ditarik!',
                            text: 'anda menarik like anda untuk game ini',
                            icon: 'error',
                        }).then(() => {
                            form.submit();
                        });
                    }
                });
            });
        });
    </script>
    <script>
        function showDownloadSuccessAlert(event) {
            // Mencegah link untuk melakukan aksi default
            event.preventDefault();

            // Menampilkan SweetAlert bahwa game berhasil diunduh
            Swal.fire({
                title: 'Download Berhasil!',
                text: 'selamat menikmati game',
                icon: 'success',
            }).then(() => {
                // Setelah SweetAlert ditutup, lanjutkan ke halaman download
                window.location.href = event.target.href;
            });
        }
    </script>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.1/dist/sweetalert2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>