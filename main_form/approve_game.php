<?php
include('../db_connect/DatabaseConnection.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $game_id = $_POST['game_id'];
    $action = $_POST['action'];

    if ($action === 'approve') {
        $query = "UPDATE games SET is_admit = 1 WHERE id_game = ?";
    } elseif ($action === 'reject') {
        $query = "DELETE FROM games WHERE id = ?";
    }

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $game_id);
    $stmt->execute();
    header('Location: admin.php'); // Redirect setelah aksi
}