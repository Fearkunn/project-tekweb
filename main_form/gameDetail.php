<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('../db_connect/DatabaseConnection.php');

// Ambil game id
$game_id = isset($_GET['game_id']) ? intval($_GET['game_id']) : 0;

// Fetch game details
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
    // Redirect or tunjukkan error jika game tidak ditemukan
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

// Periksa apakah user adalah admin
$is_admin = isset($_SESSION['role_user'])&& $_SESSION['role_user']=== 'admin';

// Check jika user sudah login
$is_logged_in = isset($_SESSION['username']) && !empty($_SESSION['username']);

if($is_logged_in){ //jika ada is logged_in tetapi jika ada username kosong / tidak ada username
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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['review_content'])) {
    // Handle review submission
    $review_content = trim($_POST['review_content']);
    $user_id = $_SESSION['user_id']; // ID pengguna dari sesi

     if ($is_logged_in && !empty($review_content)) {
        if (isset($_POST['edit_review_id'])) {
            $review_id = intval($_POST['edit_review_id']);
            $query_update_review = "UPDATE review SET review_content = ? WHERE id_review = ? AND id_user = ?";
            $stmt_update_review = $conn->prepare($query_update_review);
            $stmt_update_review->bind_param("sii", $review_content, $review_id, $user_id);
            $stmt_update_review->execute();
            $_SESSION['status'] = "edit";     
            header("Location: " . $_SERVER['PHP_SELF'] . "?game_id=" . $game_id);
            exit();
        } else {
            $query_insert_review = "INSERT INTO review (review_content, id_user, id_game) VALUES (?, ?, ?)";
            $stmt_insert_review = $conn->prepare($query_insert_review);
            $stmt_insert_review->bind_param("sii", $review_content, $user_id, $game_id);
            $stmt_insert_review->execute();
            $_SESSION['status'] = "success";     
            header("Location: " . $_SERVER['PHP_SELF'] . "?game_id=" . $game_id);
            exit();
        }
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
        $_SESSION['status'] = "delete"; 
        header("Location: " . $_SERVER['PHP_SELF'] . "?game_id=" . $game_id);
        exit();
    } else {
        $_SESSION['status'] = "error"; 
        header("Location: " . $_SERVER['PHP_SELF'] . "?game_id=" . $game_id);
        exit();
    }
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo htmlspecialchars($game['game_name']); ?> - Game Detail</title>
    <link rel="icon" href="../assets/UAP.ico" type="image/x-icon"> 
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.1/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
        background-image: url('../assets/Backgrounds.png');
        background-size: cover;
        background-repeat: no-repeat;
        background-attachment: fixed;
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
            position: relative;
        }

        .review strong {
            color: #ffffff;
        }
        .review .review-content {
            flex: 1;
        }

        .review-actions {
            display: inline;
            gap: 10px; /* Jarak antar tombol */
            justify-content: flex-start; /* Posisikan tombol ke kiri */
        }

        .review-actions button {
            flex-grow: 0; /* Tidak membesar otomatis */
            padding: 5px 10px; /* Ukuran padding tombol */
            font-size: 0.875rem; /* Ukuran teks tombol */
            border-radius: 5px; /* Untuk estetika */
            max-width: 120px; /* Batas lebar maksimum */
            white-space: nowrap; /* Hindari teks membungkus */
        }


    </style>
</head>
<body>
<?php if ($is_admin): ?>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand logo" href="admin.php">
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
<?php else: ?>  
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
                    <a href="..\auth\login.php" class="btn login-btn">Login</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
<?php endif; ?>

<div class="container game-container">
    <div class="hero-section">
        <div class="hero-image">
            <img src="<?php echo htmlspecialchars($game['games_image']); ?>" alt="<?php echo htmlspecialchars($game['game_name']); ?>">
        </div>
        <div class="game-details">
            <h1 class="game-title"><?php echo htmlspecialchars($game['game_name']); ?></h1>
            <p><strong>Publisher:</strong> <?php echo htmlspecialchars($game['publisher_name']); ?></p>
            <p><strong>Tanggal Rilis:</strong> <?php echo date('F j, Y', strtotime($game['release_date'])); ?></p>
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
    <?php if ($is_logged_in && !$is_publisher): ?>
        <!-- Add Review Form -->
        <button class="btn btn-primary my-3 " id="toggle-review-form">Tambah Review</button>
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
        <p>Silakan login sebagai user untuk menambahkan review</p>
    <?php endif; ?>
    <?php if (!empty($reviews)): ?>
        <?php foreach ($reviews as $review): ?>
            <div class="review d-flex">
                <div class="review-content">
                    <p><strong><?php echo htmlspecialchars($review['username']); ?>:</strong></p>
                    <p><?php echo nl2br(htmlspecialchars($review['review_content'])); ?></p>
                </div>
                <?php if ($is_logged_in && $username === $review['username']): ?>
                    <div class="review-actions">
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="delete_review_id" value="<?php echo htmlspecialchars($review['id_review']); ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                        </form>
                        <button class="btn btn-warning btn-sm edit-review" data-review-id="<?php echo $review['id_review']; ?>" data-review-content="<?php echo htmlspecialchars($review['review_content']); ?>">Edit</button>
                    </div>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <script>
            document.querySelectorAll('.edit-review').forEach(button => {
                button.addEventListener('click', function () {
                    const reviewId = this.dataset.reviewId;
                    const reviewContent = this.dataset.reviewContent;

                    // Tampilkan review saat ini
                    const form = document.getElementById('review-form');
                    form.querySelector('textarea').value = reviewContent; // Isi teks area dengan review baru
                    form.style.display = 'block';
                    
                    // Hapus input lama jika ada
                    const oldInput = form.querySelector('input[name="edit_review_id"]');
                    if (oldInput) {
                        oldInput.remove();
                    }

                    // Tambahkan input baru untuk review_id
                    const hiddenInput = document.createElement('input');
                    hiddenInput.type = 'hidden';
                    hiddenInput.name = 'edit_review_id';
                    hiddenInput.value = reviewId;
                    form.appendChild(hiddenInput);
                });
            });
        </script>
    <?php else: ?>
        <p>No reviews yet.</p>
    <?php endif; ?>

    
</div>
</div>
    <script>
        <?php if (isset($_SESSION['status'])): ?>
            <?php if ($_SESSION['status'] == 'success'): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Add Review Berhasil!',
                    text: 'review berhasil ditambahkan',
                });
            <?php elseif ($_SESSION['status'] == 'edit'): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Edit Review Berhasil!',
                    text: 'review berhasil diedit',
                });
            <?php elseif ($_SESSION['status'] == 'error'): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Hapus Review Gagal!',
                    text: 'silakan mencoba ulang',
                });
            <?php elseif ($_SESSION['status'] == 'delete'): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Hapus Review Berhasil!',
                    text: 'review berhasil dihapus',
                });
            <?php endif; ?>
            <?php unset($_SESSION['status']); // Hapus status setelah ditampilkan ?>
        <?php endif; ?>
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.1/dist/sweetalert2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>