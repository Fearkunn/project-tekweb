<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('../db_connect/DatabaseConnection.php');

// Pastikan user sudah login
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$game_id = isset($_GET['game_id']) ? intval($_GET['game_id']) : 0;

if ($game_id > 0) {
    // Periksa apakah game sudah ada di library user
    $query_check = "SELECT * FROM library WHERE id_user = ? AND id_game = ?";
    $stmt_check = $conn->prepare($query_check);
    $stmt_check->bind_param("ii", $user_id, $game_id);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        // Jika game sudah ada di library
        $_SESSION['message'] = "Game sudah ada di library Anda!";
    } else {
        // Jika belum, simpan game ke library
        $query_save = "INSERT INTO library (id_user, id_game) VALUES (?, ?)";
        $stmt_save = $conn->prepare($query_save);
        $stmt_save->bind_param("ii", $user_id, $game_id);

        if ($stmt_save->execute()) {
            $_SESSION['message'] = "Game berhasil disimpan ke library Anda!";
        } else {
            $_SESSION['message'] = "Terjadi kesalahan saat menyimpan game.";
        }
    }

    $stmt_check->close();
    $stmt_save->close();
}

header("Location: store.php");
exit();
?>