<?php
// Start session and include database connection
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('../db_connect/DatabaseConnection.php');
if (!isset($_SESSION['role_user'])) {
    // Redirect ke halaman login jika belum login
    header("Location: ../auth/login.php");
    exit();
}

// Check if user is logged in
$is_logged_in = isset($_SESSION['username']) && !empty($_SESSION['username']);
$user_id = $is_logged_in ? $_SESSION['username'] : '';

$role = $_SESSION['role_user'];


if (!$is_logged_in) {
    header("Location: ../auth/login.php");
    exit();
}

// Fetch user profile and role
$user_query = "
    SELECT username, user_profile, role_user
    FROM users 
    WHERE username = ?
";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("s", $user_id);
$user_stmt->execute();
$user_result = $user_stmt->get_result();

if ($user_result->num_rows > 0) {
    $user_data = $user_result->fetch_assoc();
    $profile_picture = !empty($user_data['user_profile']) && filter_var($user_data['user_profile'], FILTER_VALIDATE_URL)
        ? $user_data['user_profile']
        : "../assets/login.png";
    
    $username = htmlspecialchars($user_data['username']);
    // Fetch total number of games owned by the user
    $total_games_query = "
        SELECT COUNT(*) AS total_games
        FROM library
        WHERE id_user = (
            SELECT id_user 
            FROM users 
            WHERE username = ?
        )
    ";
    $total_games_stmt = $conn->prepare($total_games_query);
    $total_games_stmt->bind_param("s", $user_id);
    $total_games_stmt->execute();
    $total_games_result = $total_games_stmt->get_result();
    $total_games_data = $total_games_result->fetch_assoc();
    $total_games = $total_games_data['total_games'];
} else {
    // If user_id is not found in users table, check publisher table
    $publisher_query = "
        SELECT publisher_name, publisher_logo 
        FROM publisher 
        WHERE publisher_name = ?
    ";
    $publisher_stmt = $conn->prepare($publisher_query);
    $publisher_stmt->bind_param("s", $user_id);
    $publisher_stmt->execute();
    $publisher_result = $publisher_stmt->get_result();

    if ($publisher_result->num_rows > 0) {
        // If user_id is found in publisher table
        $publisher_data = $publisher_result->fetch_assoc();
        $profile_picture = !empty($publisher_data['publisher_logo']) && filter_var($publisher_data['publisher_logo'], FILTER_VALIDATE_URL)
        ? $publisher_data['publisher_logo']
        : "../assets/login.png";

        $username = htmlspecialchars($publisher_data['publisher_name']);
        
    }
    $total_games_query = "
    SELECT COUNT(*) AS total_games
    FROM games  
    WHERE id_publisher = (
        SELECT id_publisher
        FROM publisher
        WHERE publisher_name = ?
    ) AND is_admit = 1
    ";
    $total_games_stmt = $conn->prepare($total_games_query);
    $total_games_stmt->bind_param("s", $user_id);
    $total_games_stmt->execute();
    $total_games_result = $total_games_stmt->get_result();
    $total_games_data = $total_games_result->fetch_assoc();
    $total_games = $total_games_data['total_games'];
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>User Profile</title>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="icon" href="../assets/UAP.ico" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #2C2C2C;
        }
        #profile-section{
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            background-image: url('../assets/Background.png');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center top;
            margin: 0;
            padding: 0;
        }
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
        .user-profile {
            text-align: center;
            margin-top: 50px;
        }
        .user-profile img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 20px;
        }
        .stats {
            margin-top: 30px;
            font-size: 1.2rem;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand mx-auto" href="../main_form/mainForm.php">
                <img src="../assets/UapLogoText.svg" alt="UapLogo">
            </a>
        </div>
    </nav>

    <section id="profile-section">
        <div class="container text-light">
                
        <!-- User Profile Section -->
            <div class="user-profile">
                <img src="<?php echo $profile_picture; ?>" alt="Profile Picture">
                <h1><?php echo $username; ?></h1>
                <p>Role: <?php echo $role; ?></p>
            </div>

            <div class="text-center">
                <a href="changeProfilePicture.php" class="btn btn-primary">Edit Profile</a>
                <a href="changePassword.php" class="btn btn-warning">Ganti Password</a>
                <a href="changeUsername.php" class="btn btn-info">Ganti Username</a>
                <a href="deleteAccount.php" class="btn btn-danger">Hapus Akun</a>
            </div>


            <!-- Stats Section -->
             <?php if ($role == "PUBLISHER") : ?>
            <div class="stats text-center">
                <p><strong>Jumlah Game Yang Telah Dipublish:</strong> <?php echo $total_games; ?></p>
            </div>
            <?php endif; ?>
            <?php if ($role == "USER") : ?>
                <div class="stats text-center">
                    <p><strong>Jumlah Game Yang Dimiliki:</strong> <?php echo $total_games;?></p>
                </div>
            <?php endif;?>
        </div>
    </section>
</body>
</html>