<?php
session_start();
include ('C:\xampp\htdocs\project tekweb\project-tekweb-1\db_connect\DatabaseConnection.php');

?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>login_page</title>
</head>
<body>
  <h2>Login: </h2>
  <form action="login.php" method="post">
    <label for="username">Username: </label>
    <input type="text" name="username" required><br><br>

    <label for="password">Password: </label>
    <input type="password" name="password" required><br><br>
    <button class="submit">Submit</button>
  </form>
</body>
</html>