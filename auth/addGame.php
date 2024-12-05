<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

include('../db_connect/DatabaseConnection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

//  if (!isset($_SESSION['publisher_id'])) {
    // Jika tidak ada publisher yang login, arahkan ke halaman login
//    header("Location: ../auth/login.php");
//    exit;
//  }
  
  // Ambil data dari form
    $gameName = trim($_POST['gameName'] ?? '');
    $gameDescription = trim($_POST['gameDescription'] ?? '');
    $releaseDate = trim($_POST['releaseDate'] ?? '');
    $genres = $_POST['genres'] ?? [];
    $publisherId = 1; // Publisher ID hardcoded (gantilah sesuai publisher yang login)
    $gameImage = isset($_FILES['gameImage']) ? file_get_contents($_FILES['gameImage']['tmp_name']) : null;

    // Validasi input
    $errors = [];
    if (!$gameName) $errors[] = "Game name is required.";
    if (!$releaseDate) $errors[] = "Release date is required.";
    if (empty($genres)) $errors[] = "You must select at least one genre.";
    if (!$gameImage) $errors[] = "Game image is required.";

    // Jika ada error, return error message
    if (!empty($errors)) {
        echo json_encode(['success' => false, 'message' => implode('<br>', $errors)]);
        exit;
    }

    // Masukkan data game ke tabel games
    $stmt = $conn->prepare("INSERT INTO games (game_name, game_desc, is_admit, release_date, game_image, id_publisher) VALUES (?, ?, false, ?, ?, ?)");
    $stmt->bind_param("ssssi", $gameName, $gameDescription, $releaseDate, $gameImage, $publisherId);
    $stmt->execute();

    // Ambil ID game yang baru dimasukkan
    $gameId = $stmt->insert_id;

    // Masukkan genre ke detail_genre
    $stmtGenre = $conn->prepare("INSERT INTO detail_genre (id_game, id_genre) VALUES (?, ?)");
    foreach ($genres as $genreId) {
        $stmtGenre->bind_param("ii", $gameId, $genreId);
        $stmtGenre->execute();
    }

    // Return success response
    echo json_encode([
        'success' => true,
        'game_name' => $gameName,
        'game_description' => $gameDescription,
        'game_image' => $gameImage
    ]);
}

include('../auth/cookieValidation.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" href= "../assets/UAP.ico" type="image/x-icon"> 
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <title>Add Game</title>
</head>
<style>
   /* Styling for Navbar and Cards */
   .navbar {
        background-color: #2C2C2C;
        font-family: Arial, sans-serif;
        padding: 10px 20px;
    }
    .navbar-brand, .nav-link {
        color: #FFFFFF !important;
    }
    .navbar-brand {
        font-weight: bold;
        font-size: 1.5rem;
    }

    .card {
      cursor: pointer;
      transition: transform 0.2s ease;
    }
    .card:hover {
      transform: scale(1.05);
    }
    .icon-plus {
      font-size: 2rem;
      text-align: center;
      color: #007bff;
    }
    #MainSection {
        background-image: url('../assets/Background.png');
        background-size: cover;
        background-repeat: no-repeat;
        background-position: center top;
        height: 100vh;
        display: flex;
        justify-content: center;
    }
    .card {
        min-height: 300px;
    }
    .card-img-top {
        object-fit: cover;
        height: 150px;
    }
  </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand mx-auto" href="..\main_form\mainForm.php">
                <img src="..\assets\UapLogoText.svg" alt="UapLogo">
            </a>
        </div>
    </nav>

    <!-- Main Section with Game Cards -->
    <section id="MainSection">
        <div class="container mt-5">
            <div class="row g-3" id="gameCardContainer">
                <!-- First Card: Add Game Button -->
                <div class="col-md-3">
                    <div class="card text-center h-100" id="addGameCard" data-bs-toggle="modal" data-bs-target="#gameModal">
                        <div class="card-body d-flex justify-content-center align-items-center">
                            <i class="bi bi-plus-circle icon-plus"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal for Adding Game -->
        <div class="modal fade" id="gameModal" tabindex="-1" aria-labelledby="gameModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="gameModalLabel">Tambahkan Game Baru</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="gameForm" method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label for="gameName" class="form-label">Nama Game</label>
                                <input type="text" class="form-control" name="gameName" required>
                            </div>
                            <div class="mb-3">
                                <label for="gameDescription" class="form-label">Deskripsi Game</label>
                                <textarea class="form-control" name="gameDescription" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="releaseDate" class="form-label">Release Date</label>
                                <input type="date" class="form-control" name="releaseDate" required>
                            </div>
                            <div class="mb-3">
                                <label for="gameImage" class="form-label">Gambar Game</label>
                                <input type="file" class="form-control" name="gameImage" accept="image/*" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Genre Game</label>
                                <div class="row">
                                    <?php
                                    $genres = [
                                        1 => 'Action', 2 => 'Adventure', 3 => 'Casual', 4 => 'RPG', 5 => 'Simulation',
                                        6 => 'Strategy', 7 => 'Sports', 8 => 'Racing', 9 => 'Indie', 10 => 'Early Access',
                                        11 => 'Multiplayer', 12 => 'Singleplayer', 13 => 'Action-Adventure', 14 => 'Horror',
                                        15 => 'Shooter', 16 => 'Survival', 17 => 'Platformer', 18 => 'Puzzle',
                                        19 => 'Visual Novel', 20 => 'Metroidvania', 21 => 'Open World', 
                                        22 => 'Sci-Fi & Cyberpunk', 23 => 'Fantasy', 24 => 'Simulation - Space & Flight',
                                        25 => 'Building & Automation', 26 => 'Card & Board', 27 => 'Turn-Based Strategy',
                                        28 => 'Real-Time Strategy', 29 => 'MMORPG'
                                    ];
                                    foreach ($genres as $id => $genre) {
                                        echo "
                                        <div class='col-6 col-md-4'>
                                            <div class='form-check'>
                                                <input class='form-check-input' type='checkbox' name='genres[]' value='$id' id='genre$id'>
                                                <label class='form-check-label' for='genre$id'>$genre</label>
                                            </div>
                                        </div>";
                                    }
                                    ?>
                                </div>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">Tambahkan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- JavaScript and AJAX Logic -->
    <script>
        const gameForm = document.getElementById('gameForm');
        gameForm.addEventListener('submit', function(event) {
            event.preventDefault();

            const formData = new FormData(gameForm);

            fetch('addGame.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Update the first card with the game information
                    const gameCardContainer = document.getElementById('gameCardContainer');

                    const newGameCard = document.createElement('div');
                    newGameCard.classList.add('col-md-3');
                    newGameCard.innerHTML = `
                        <div class="card text-center h-100">
                            <img src="${data.game_image}" class="card-img-top" alt="Game Image">
                            <div class="card-body">
                                <h5 class="card-title">${data.game_name}</h5>
                                <p class="card-text">${data.game_description}</p>
                            </div>
                        </div>
                    `;

                    // Remove the "Add Game" card and append the new game card
                    const addGameCard = document.getElementById('addGameCard');
                    addGameCard.parentNode.removeChild(addGameCard);
                    gameCardContainer.appendChild(newGameCard);

                    // Add a new "Add Game" card for the next game
                    const addNewGameCard = document.createElement('div');
                    addNewGameCard.classList.add('col-md-3');
                    addNewGameCard.innerHTML = `
                        <div class="card text-center h-100" id="addGameCard" data-bs-toggle="modal" data-bs-target="#gameModal">
                            <div class="card-body d-flex justify-content-center align-items-center">
                                <i class="bi bi-plus-circle icon-plus"></i>
                            </div>
                        </div>
                    `;
                    gameCardContainer.appendChild(addNewGameCard);
                } else {
                    alert('Failed to add the game');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Something went wrong!');
            });
        });
    </script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>