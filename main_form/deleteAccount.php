<?php
session_start();

// Jika tidak ada sesi username, arahkan ke login
if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

include('../db_connect/DatabaseConnection.php');

$error = '';
$success = '';
$current_username = $_SESSION['username'];

// Cek apakah username ada di tabel `users` atau `publisher`
$user_query = "SELECT * FROM users WHERE username = ?";
$user_stmt = $conn->prepare($user_query);
$user_stmt->bind_param("s", $current_username);
$user_stmt->execute();
$user_result = $user_stmt->get_result();

if ($user_result->num_rows > 0) {
    // Jika username ditemukan di tabel `users`
    $table = "users";
    $column = "username";
} else {
    // Periksa di tabel `publisher`
    $publisher_query = "SELECT * FROM publisher WHERE publisher_name = ?";
    $publisher_stmt = $conn->prepare($publisher_query);
    $publisher_stmt->bind_param("s", $current_username);
    $publisher_stmt->execute();
    $publisher_result = $publisher_stmt->get_result();

    if ($publisher_result->num_rows > 0) {
        // Jika username ditemukan di tabel `publisher`
        $table = "publisher";
        $column = "publisher_name";
    } else {
        // Jika username tidak ditemukan di kedua tabel, logout
        session_destroy();
        header("Location: ../auth/login.php");
        exit();
    }
}

// Jika metode POST digunakan untuk menghapus akun
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $delete_query = "DELETE FROM $table WHERE $column = ?";
    $delete_stmt = $conn->prepare($delete_query);
    $delete_stmt->bind_param("s", $current_username);

    if ($delete_stmt->execute()) {
        // Hapus sesi dan arahkan ke halaman login
        session_destroy();
        header("Location: ../auth/login.php");
        exit();
    } else {
        $error = "Failed to delete account.";
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Delete Account</title>
    <link rel="icon" href="../assets/UAP.ico" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { 
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #2C2C2C; 
        }
        #delete-section{
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
        .delete-account-box { 
            background-color: rgba(0, 0, 0, 0.8);
            padding: 30px 25px;
            border-radius: 10px;
            color: white;
            width: 100%;
            max-width: 400px;
            margin: 10px 0; 
        }
        .container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .btn-danger { background-color: #dc3545; border: none; width: 100%; }
        .btn-danger:hover { background-color: #b02a37; }
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

    <section id="delete-section">
        <div class="container">
            <div class="delete-account-box">
                <h2 class="pb-3">Delete Account</h2>
                <?php if ($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>
                <form id="delete-form" method="POST">
                    <p class="pb-2">Are you sure you want to delete your account? This action is permanent.</p>
                    <button type="button" class="btn btn-danger" id="confirm-delete-btn">Delete Account</button>
                </form>
            </div>
        </div>
    </section>

    <script>
        document.getElementById('confirm-delete-btn').addEventListener('click', function() {
            Swal.fire({
                title: 'Are you sure?',
                text: 'This action is permanent and cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Yes, delete it!',
                cancelButtonText: 'No, cancel!'
            }).then((result) => {
                if (result.isConfirmed) {
                    // If user confirms, submit the form
                    document.getElementById('delete-form').submit();
                }
            });
        });
    </script>
</body>
</html>