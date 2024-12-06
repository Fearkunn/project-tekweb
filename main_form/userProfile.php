<?php
// Start session and include database connection
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('../db_connect/DatabaseConnection.php');

// Check if user is logged in
$is_logged_in = isset($_SESSION['username']) && !empty($_SESSION['username']);
$user_id = $is_logged_in ? $_SESSION['username'] : '';

if (!$is_logged_in) {
    header("Location: ../auth/login.php");
    exit;
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
    $role = htmlspecialchars($user_data['role_user']);
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
        $role = "PUBLISHER";
        $username = htmlspecialchars($publisher_data['publisher_name']);
        $total_games = "N/A"; // Publisher doesn't have a games library
    }
}
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>User Profile</title>
    <link rel="icon" href="../assets/UAP.ico" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-image: url('../assets/Background.png');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center top;
            color: #FFFFFF;
        }
        .navbar {
            background-color: #2C2C2C;
        }
        .navbar-brand, .nav-link {
            color: #FFFFFF !important;
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
            <a class="navbar-brand" href="mainForm.php">Profile</a>
            <a href="../auth/logout.php" class="btn btn-danger">Logout</a>
        </div>
    </nav>

    <div class="container">
            
    <!-- User Profile Section -->
        <div class="user-profile">
            <img src="<?php echo $profile_picture; ?>" alt="Profile Picture">
            <h1><?php echo $username; ?></h1>
            <p>Role: <?php echo $role; ?></p>
        </div>

        <div class="text-center">
            <a href="changeProfilePicture.php" class="btn btn-primary">Edit Profile</a>
            <a href="changePassword.php" class="btn btn-warning">Change Password</a>
            <a href="changeUsername.php" class="btn btn-info">Change Username</a>
            <a href="deleteAccount.php" class="btn btn-danger">Delete Account</a>
        </div>


        <!-- Stats Section -->
        <div class="stats text-center">
            <p><strong>Total Games Owned:</strong> <?php echo $total_games; ?></p>
        </div>
    </div>
</body>
</html>