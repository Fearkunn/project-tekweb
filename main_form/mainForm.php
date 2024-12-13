<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


include('../db_connect/DatabaseConnection.php');


$is_logged_in = isset($_SESSION['username']) && !empty($_SESSION['username']);

if($is_logged_in){ //jika ada is logged_in tapi jika ga ada username (kosong)
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

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Uap</title>
    <link rel="icon" href= "../assets/UAP.ico" type="image/x-icon"> 
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

    .carousel-item {
    transition: transform 0.5s ease-in-out;
    }

    .game-image-container img {
        box-shadow: 0 4px 15px rgba(0, 0, 0, 0.6);
    }

    .carousel-caption {
        background: rgba(0, 0, 0, 0.8);
        padding: 20px;
        border-radius: 8px;
    }

    .carousel-control-prev-icon,
    .carousel-control-next-icon {
        background-color: #ff4444;
        border-radius: 50%;
    }

    .btn-danger {
        background-color: #ff4444;
        border: none;
        transition: Backgrounds 0.3s;
    }

    .btn-danger:hover {
        background-color: #ff6666;
    }

    #gameSlider{
        display: flex;
        justify-content: center;
        align-items: center;
    }

    #gameBox{
        display:flex;
        justify-content: center;
        align-items: center;
    }

    .badge {
        font-size: 0.9rem;
        padding: 0.4em 0.8em;
        border-radius: 12px;
        text-transform: capitalize;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        transition: transform 0.2s;
        background-color: #2C2C2C;
    }

    .badge:hover {
        transform: scale(1.1);
    }
        .card {
        background-color: #333;
        border-radius: 8px;
        border: none;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        display: flex;
        flex-direction: row;
    }

    .card:hover {
        transform: scale(1.05);
        box-shadow: 0 8px 16px rgba(0, 0, 0, 0.3);
    }

    .card-img-left {
        border-radius: 8px;
        width: 350px; /* Size gambar yang lebih besar */
        height: 200px; /* Ukuran tinggi gambar */
        object-fit: cover;
    }

    .card-body {
        flex: 1;
        padding: 20px;
    }

    .card-title {
        font-size: 1.5rem;
        font-weight: bold;
        color: #e60000;
    }

    .card-text {
        font-size: 1rem;
        color: #ccc;
        margin-bottom: 15px;
    }
  
    </style>
</head>
<header>
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
    
    <section id="loop-video" style="position:relative;">
        <div class="video-section " style="z-index:1; position:relative;height:500px;">
            <video autoplay muted loop class="w-100" style="height: 500px; object-fit: cover; z-index:1; position:relative;">
                <source src="https://shared.cloudflare.steamstatic.com/store_item_assets/steam/clusters/frontpage/208fc14a272e0d69ee83b286/webm_page_bg_english.webm?t=1732662057" type="video/mp4">
                Browser Anda tidak mendukung video HTML5.
            </video>
        </div>
    </section>

  


</header>
<body>

<section id="carousel">
    <!-- FITUR REKOMENDASI GAME -->
    <div class="container mt-5">
        <h1 class="text-center mb-4 text-warning">Featured Free Games</h1>
        <div id="gameCarousel" class="carousel slide" data-bs-ride="carousel">
            <div class="carousel-inner">
                <?php
                $games = [];
                $query = "
                    SELECT g.id_game, g.game_name, g.games_image, g.game_desc, g.release_date, g.like_count,
                        GROUP_CONCAT(DISTINCT genre.genre_name SEPARATOR ', ') AS genres
                    FROM games g
                    LEFT JOIN detail_genre dg ON g.id_game = dg.id_game
                    LEFT JOIN genre ON dg.id_genre = genre.id_genre
                    WHERE g.is_admit = 1
                    GROUP BY g.id_game
                    ORDER BY g.like_count DESC
                    LIMIT 5;
                ";
                $result = $conn->query($query);
                if ($result && $result->num_rows > 0):
                    while ($game = $result->fetch_assoc()):
                        $games[] = $game;
                    endwhile;

                    foreach ($games as $index => $game):
                        $isActive = ($index === 0) ? 'active' : '';
                        $genres = explode(',', $game['genres']);

                        // Pengecekan apakah game sudah ada di library
                        $query = "SELECT * FROM library WHERE id_user = ? AND id_game = ?";
                        $stmt = $conn->prepare($query);
                        $stmt->bind_param("ii", $_SESSION['user_id'], $game['id_game']);
                        $stmt->execute();
                        $is_in_library = $stmt->get_result()->num_rows > 0;

                        // Handle form submission (Add to Library)
                        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_library']) && $_POST['id_game'] == $game['id_game']) {
                            // Add game to library if not already there
                            if (!$is_in_library) {
                                $query = "INSERT INTO library (id_user, id_game) VALUES (?, ?)";
                                $stmt = $conn->prepare($query);
                                $stmt->bind_param("ii", $_SESSION['user_id'], $game['id_game']);
                                $stmt->execute();
                                $is_in_library = true; // Mark as added to library after insertion
                            }
                            $_SESSION['game_added'] = true;
                        }
                ?>
                <!-- CAROUSEL -->
                        <div class="carousel-item <?= $isActive ?>">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="game-image-container" style="height: 400px; overflow: hidden;">
                                        <img src="<?= htmlspecialchars($game['games_image']) ?>" 
                                             class="d-block mx-auto" 
                                             alt="<?= htmlspecialchars($game['game_name']) ?>" 
                                             style="width:100%;object-fit: fill;object-position:top; border-radius: 8px;">
                                    </div>
                                </div>
                                <div class="col-md-8 d-flex flex-column justify-content-center bg-dark text-white p-4" style="border-radius: 8px;">
                                    <h3 class="text-warning"><?= htmlspecialchars($game['game_name']) ?></h3>
                                    <p><?= htmlspecialchars($game['game_desc']) ?></p>
                                    <p>Released: <?= date('d M Y', strtotime($game['release_date'])) ?></p>
                                    <p>Likes: <?= $game['like_count'] ?></p>
                                    <div class="mt-2">
                                        <h5 class="text-light">Genres:</h5>
                                        <div class="d-flex flex-wrap gap-2">
                                            <?php foreach ($genres as $genre): ?>
                                                <span class="badge bg-secondary"><?= htmlspecialchars(trim($genre)) ?></span>
                                            <?php endforeach; ?>
                                        </div>

                                        <!-- Tombol-tombol disusun berdampingan -->
                                        <div class="d-flex justify-content-start gap-2 mt-3">
                                            <a href="gameDetail.php?game_id=<?= $game['id_game'] ?>" class="btn btn-primary btn-sm">View Details</a>
                                            <?php if (!$is_publisher && !$is_in_library && $is_logged_in): ?>
                                                <!-- Form to add to library directly in this page -->
                                                <form method="POST" id="addToLibraryForm">
                                                    <input type="hidden" name="id_game" value="<?= $game['id_game'] ?>">
                                                    <button type="submit" name="add_to_library" class="btn btn-danger btn-sm">Add to Library</button>
                                                </form>
                                            <?php elseif (!$is_publisher && $is_logged_in): ?>
                                                <button class="btn btn-success btn-sm" disabled>Already in Library</button>
                                            <?php endif; ?>
                                        </div>
                                        
                                        <!-- SweetAlert setelah game ditambahkan -->
                                        <?php if (isset($_SESSION['game_added']) && $_SESSION['game_added']): ?>
                                            <script>
                                                document.addEventListener('DOMContentLoaded', function() {
                                                    Swal.fire({
                                                        title: "Game Berhasil Ditambahkan!",
                                                        text: "silakan cek di library",
                                                        icon: "success"
                                                    });then(() => {
                                                    window.location.href = "../main_form/mainForm.php";
                                                });
                                              });
                                            </script>
                                            <?php unset($_SESSION['game_added']); ?>
                                        <?php endif; ?> 
                                    </div>
                                </div>
                            </div>
                        </div>
                <?php
                    endforeach;
                else:
                ?>
                    <div class="carousel-item active">
                        <div class="text-center p-5" style="background: #121212; color: #fff;">
                            <h5>No Games Available</h5>
                            <p>Please add games to the database.</p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#gameCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#gameCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>
    </div>
</section>


<section>
    <div class="container mt-5">
        <h2 class="text-center mb-4">All Free Games</h2>
        <div class="row">
            <?php
            $query = "
                SELECT g.id_game, g.game_name, g.games_image, g.game_desc, g.release_date, g.like_count,
                    GROUP_CONCAT(distinct genre.genre_name SEPARATOR ', ') AS genres
                FROM games g
                LEFT JOIN detail_genre dg ON g.id_game = dg.id_game
                LEFT JOIN genre ON dg.id_genre = genre.id_genre
                WHERE g.is_admit = 1
                GROUP BY g.id_game
                ORDER BY g.like_count DESC
                LIMIT 15 OFFSET 5;
            ";
            $result = $conn->query($query);
            if ($result && $result->num_rows > 0):
                while ($game = $result->fetch_assoc()):
                    $genres = $game['genres'] ? explode(',', $game['genres']) : [];

                    // Pengecekan apakah game sudah ada di library
                    $query = "SELECT * FROM library WHERE id_user = ? AND id_game = ?";
                    $stmt = $conn->prepare($query);
                    $stmt->bind_param("ii", $_SESSION['user_id'], $game['id_game']);
                    $stmt->execute();
                    $is_in_library = $stmt->get_result()->num_rows > 0;

                    // Handle form submission (Add to Library)
                    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_to_library']) && $_POST['id_game'] == $game['id_game']) {
                        // Add game to library if not already there
                        if (!$is_in_library) {
                            $query = "INSERT INTO library (id_user, id_game) VALUES (?, ?)";
                            $stmt = $conn->prepare($query);
                            $stmt->bind_param("ii", $_SESSION['user_id'], $game['id_game']);
                            $stmt->execute();
                            $is_in_library = true; // Mark as added to library after insertion
                        }
                        $_SESSION['game_added'] = true;
                    }
            ?>
                    <div class="col-12 mb-2">
                        <a href="gameDetail.php?game_id=<?= $game['id_game'] ?>" style="text-decoration: none; color: inherit;">
                        <div class="card d-flex flex-row h-100">
                            <img src="<?= $game['games_image'] ?>" class="card-img-left" alt="<?= $game['game_name'] ?>" style="object-fit: cover; border-radius: 8px; padding: 2px">
                            <div class="card-body p-2">
                                <h5 class="card-title" style="font-size: 1.3rem;"><?= htmlspecialchars($game['game_name']) ?></h5>
                                <p class="card-text" style="font-size: 0.75rem;">Released: <?= date('d M Y', strtotime($game['release_date'])) ?></p>
                                <p class="card-text" style="font-size: 0.75rem;">Likes: <?= $game['like_count'] ?></p>
                                <div class="mb-2">
                                    <h6 style="color: white;">Genres:</h6>
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php foreach ($genres as $genre): ?>
                                            <span class="badge bg-secondary"><?= htmlspecialchars(trim($genre)) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php if (!$is_publisher && !$is_in_library && $is_logged_in): ?>
                                    <!-- Form to add to library directly in this page -->
                                    <form method="POST">
                                        <input type="hidden" name="id_game" value="<?= $game['id_game'] ?>">
                                        <button type="submit" name="add_to_library" class="btn btn-danger btn-sm mt-3">Add to Library</button>
                                    </form>
                                    <?php if (isset($_SESSION['game_added']) && $_SESSION['game_added']): ?>
                                        <script>
                                            document.addEventListener('DOMContentLoaded', function() {
                                                Swal.fire({
                                                    title: "Game Berhasil Ditambahkan!",
                                                    text: "silakan cek di library",
                                                    icon: "success"
                                                });
                                                header("Location: ../main_form/mainForm.php")
                                            });
                                        </script>
                                    <?php unset($_SESSION['game_added']); ?>
                                    <?php endif; ?>
                                <?php elseif (!$is_publisher && $is_logged_in): ?>
                                    <button class="btn btn-success btn-sm mt-3" disabled>Already in Library</button>
                                <?php endif; ?>
                            </div>
                        </div>
                        </a>
                    </div>
            <?php
                endwhile;
            else:
            ?>
                <div class="col-12">
                    <p class="text-center">No games available. Please add games to the database.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.1/dist/sweetalert2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>