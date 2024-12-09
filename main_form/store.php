<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('../db_connect/DatabaseConnection.php');

$is_logged_in = isset($_SESSION['username']) && !empty($_SESSION['username']);
$username = $is_logged_in ? $_SESSION['username'] : '';

// Search filter
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Query semua game
$query = "SELECT * FROM games";
if (!empty($search)) {
    $query .= " WHERE game_name LIKE ?";
}
$stmt = $conn->prepare($query);

if (!empty($search)) {
    $searchParam = "%" . $search . "%";
    $stmt->bind_param("s", $searchParam);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Store</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
        .store-header {
            background: linear-gradient(rgba(200, 14, 49, 0.8), rgba(125, 7, 23, 0.8));
            color: white;
            text-align: center;
            padding: 20px;
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
<div class="store-header">
    <a href="../main_form/mainForm.php" class="text-decoration-none text-dark">
        <h1 style="color:white;">Game Store</h1>
    </a>
    <p>Temukan game favoritmu!</p>
</div>

    <!-- Search Bar -->
    <div class="search-bar text-center">
        <form method="GET" action="">
            <input type="text" name="search" placeholder="Cari game..." value="<?php echo htmlspecialchars($search); ?>">
            <button type="submit">Search</button>
        </form>
    </div>

    <!-- Game Cards -->
    <div class="container">
        <div class="row">
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                        <div class="card card-game">
                            <img src="<?php echo htmlspecialchars($row['games_image']); ?>" alt="<?php echo htmlspecialchars($row['game_name']); ?>" class="card-img-top">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($row['game_name']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars(substr($row['game_desc'], 0, 50)) . '...'; ?></p>
                                <a href="gameDetail.php?game_id=<?php echo $row['id_game']; ?>"  class="btn btn-primary">View Game</a>
                                <a href="saveGame.php?game_id=<?php echo $row['id_game']; ?>" class="btn btn-success">Save Game</a>

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