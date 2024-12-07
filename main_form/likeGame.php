<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('../db_connect/DatabaseConnection.php');

// Cek apakah user sudah login
if (!isset($_SESSION['user_id']) || empty($_SESSION['user_id'])) {
    echo json_encode(['message' => 'User not logged in.']);
    exit();
}

$user_id = $_SESSION['user_id'];
$data = json_decode(file_get_contents('php://input'), true);
$game_id = isset($data['game_id']) ? intval($data['game_id']) : 0;
$liked = isset($data['liked']) ? $data['liked'] : false;

if ($game_id > 0) {
    if ($liked) {
        // Tambahkan like
        $query = "UPDATE games SET like_count = like_count + 1 WHERE id_game = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $game_id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['message' => 'Game liked successfully.']);
    } else {
        // Kurangi like
        $query = "UPDATE games SET like_count = GREATEST(like_count - 1, 0) WHERE id_game = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $game_id);
        $stmt->execute();
        $stmt->close();
        echo json_encode(['message' => 'Game unliked successfully.']);
    }
} else {
    echo json_encode(['message' => 'Invalid game ID.']);
}
?>