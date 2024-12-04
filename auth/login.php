<?php
session_start();
include ('C:\xampp\htdocs\Uap\db_connect\DatabaseConnection.php');
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <style>
        /* Custom Steam-like navbar style */
        .navbar {
            background-color: #1b2838;
            font-family: Arial, sans-serif;
        }
        .navbar-brand, .nav-link {
            color: #c7d5e0 !important;
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 1.25rem;
        }
        .navbar-nav .nav-link:hover {
            color: #66c0f4 !important;
        }
        .nav-link {
            margin-right: 1.5rem;
        }
        .navbar-toggler {
            border-color: #66c0f4;
        }
        .login-btn {
            border: 1px solid #66c0f4;
            padding: 5px 10px;
            border-radius: 3px;
            text-decoration: none;
            color:white;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">My Steam</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarScroll" aria-controls="navbarScroll" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse justify-content-center" id="navbarScroll">
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
                        <a class="nav-link" href="#">Username Profile</a>
                    </li>
                </ul>
                <a href="login.php" class="login-btn">Login</a>  
            </div>
        </div>
    </nav>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
</body>
</html>