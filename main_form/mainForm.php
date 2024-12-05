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





// Check if the user is logged in via session
include('../auth/cookieValidation.php');
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Uap</title>
    <link rel="icon" href= "../assets/UAP.ico" type="image/x-icon"> 
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
    #MainSection {
        background-image: url('https://i.im.ge/2024/11/16/zTTkxF.Background.png');
        background-size: cover;
        background-repeat: no-repeat;
        background-position: center top;
        height: 100vh;
        display: flex;
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


    .carousel-item img {
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
    }
    .carousel-caption {
        background-color: rgba(0, 0, 0, 0.6);
        padding: 10px;
        border-radius: 5px;
    }

    #gameSlider{
        display: flex;
        justify-content: center;
        align-items: center;
    }

    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand logo" href="#" >
                    <img src="..\assets\Logo.svg" alt="UapLogo">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarScroll" aria-controls="navbarScroll" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-center navbar-abc" id="navbarScroll">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="#">Store</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Library</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#">Community</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link disabled" aria-disabled="true" href="#"><?php echo $username; ?></a>
                    </li>
                    <?php if ($is_publisher): ?>
                        <li class="nav-item">
                            <a href="../main_form/addGame.php" class="nav-link">Add Game</a>
                        </li>
                    <?php endif; ?>    
                </ul>
                <?php if ($is_logged_in): ?>
                    <div class="dropdown" style="background-color: #2C2C2C;">
                        <button class=" btn btn-secondary dropdown-toggle bi bi-person-circle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" style="font-size: 1.8rem; background-color: #2C2C2C;" aria-expanded="false"><?php echo " ",$username; ?></button>
                        <ul class="dropdown-menu bg-dark" aria-labelledby="dropdownMenuButton1">
                            <li><a class="dropdown-item" href="#">Action</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="../auth/logout.php">Change account</a></li>
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
                <source src="https://shared.fastly.steamstatic.com/store_item_assets/steam/clusters/frontpage/b04ec5ca66d2105a0fccc116/webm_page_bg_indonesian.webm?t=1731704947" type="video/mp4">
                Browser Anda tidak mendukung video HTML5.
            </video>
        </div>
    </section>

    <!-- Navbar Kedua -->
     <section id="subnavbar-game" style="position: absolute; top: 100px; left: 0; width: 100%; z-index: 2;">
        <nav class="navbar navbar-expand-lg navbar-dark mt-3 mx-auto" style="width:940px; length:66px; padding:0; ">
            <div class="container navbar-custom">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link text-white" href="#">Toko</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="#">Baru & Patut Dicoba</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="#">Kategori</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="#">Toko Poin</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="#">Berita</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-white" href="#">Lab</a>
                    </li>
                </ul>
            </div>
        </nav>
    </section>

    
    <section id="MainSection">
        <div class="container">
            <div id="gameSlider" class="carousel slide" data-bs-ride="carousel">
                <div class="carousel-inner">
                    <!-- Static Images -->
                    <div class="carousel-item active" style="height: 100%;">
                        <img src="https://via.placeholder.com/940x400?text=Game+1" class="d-block w-100" alt="Game 1">
                        <div class="carousel-caption d-none d-md-block">
                            <h5>Game 1</h5>
                            <p>Placeholder for Game 1 description.</p>
                        </div>
                    </div>
                    <div class="carousel-item" style="height: 100%;">
                        <img src="https://via.placeholder.com/940x400?text=Game+2" class="d-block w-100" alt="Game 2">
                        <div class="carousel-caption d-none d-md-block">
                            <h5>Game 2</h5>
                            <p>Placeholder for Game 2 description.</p>
                        </div>
                    </div>
                    <div class="carousel-item" style="height: 100%;">
                        <img src="https://via.placeholder.com/940x400?text=Game+3" class="d-block w-100" alt="Game 3">
                        <div class="carousel-caption d-none d-md-block">
                            <h5>Game 3</h5>
                            <p>Placeholder for Game 3 description.</p>
                        </div>
                    </div>
                </div>
                <!-- Navigation -->
                <button class="carousel-control-prev" type="button" data-bs-target="#gameSlider" data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Previous</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#gameSlider" data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Next</span>
                </button>
            </div>
        </div>
        
    </section>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>