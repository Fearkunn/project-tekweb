<?php
session_start();
include('../db_connect/DatabaseConnection.php');
?>


<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background-color: #2C2C2C;
            color: white;
            height: 100%;
            width: 100%;
            background-image: url('https://i.im.ge/2024/11/16/zTTkxF.Background.png');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center top;
        }

     
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
        .navbar-nav .nav-link:hover {
            color: #FF4C4C !important; /* Merah terang saat hover */
        }
        .nav-link {
            margin-right: 1.5rem;
        }
        .navbar-toggler {
            border-color: #FFFFFF; /* Tanda toggle putih */
        }

        .container {
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-box {
            background-color: rgba(0, 0, 0, 0.8);
            padding: 20px;
            border-radius: 8px;
            color: white;
            width: 100%;
            max-width: 400px;
        }

        .login-box h2 {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand mx-auto" href="..\main_form\mainForm.php">
                <img src="\Uap\assets\UapLogoText.svg" alt="UapLogo">
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarScroll" aria-controls="navbarScroll" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
        
        </div>
    </nav>

    

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>