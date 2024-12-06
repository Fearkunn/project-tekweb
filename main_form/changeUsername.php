<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../auth/login.php");
    exit();
}

include('../db_connect/DatabaseConnection.php');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_username = $_POST['new_username'];

    if (empty($new_username)) {
        $error = "Username cannot be empty!";
    } else {
        // Check if the new username already exists in the database
        $query = "SELECT * FROM users WHERE username = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("s", $new_username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            $error = "Username already taken!";
        } else {
            $current_username = $_SESSION['username'];
            $update_query = "UPDATE users SET username = ? WHERE username = ?";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->bind_param("ss", $new_username, $current_username);

            if ($update_stmt->execute()) {
                // Update session with the new username
                $_SESSION['username'] = $new_username;
                $success = "Username successfully updated!";
            } else {
                $error = "Failed to update username.";
            }
        }
    }
}
?>

<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Change Username</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #2C2C2C; color: white; font-family: Arial, sans-serif; }
        .container { display: flex; justify-content: center; align-items: center; height: 100vh; }
        .form-box { background-color: rgba(0, 0, 0, 0.8); padding: 30px 25px; border-radius: 10px; width: 100%; max-width: 400px; }
        .btn-primary { background-color: #007bff; border: none; width: 100%; }
        .btn-primary:hover { background-color: #0056b3; }
        .btn-secondary { background-color: #6c757d; border: none; width: 100%; margin-top: 10px; }
        .btn-secondary:hover { background-color: #5a6268; }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-box">
            <h2>Change Username</h2>
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
                <a href="userProfile.php" class="btn btn-secondary">Back to Profile</a>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label for="new_username" class="form-label">New Username</label>
                    <input type="text" class="form-control" name="new_username" id="new_username" required>
                </div>
                <button type="submit" class="btn btn-primary">Update Username</button>
            </form>
        </div>
    </div>
</body>
</html>