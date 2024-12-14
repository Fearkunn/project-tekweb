<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('../db_connect/DatabaseConnection.php');

// Check jika game id ada di DB
if (!isset($_GET['game_id']) || empty($_GET['game_id'])) {
    die("Invalid request.");
}

$game_id = intval($_GET['game_id']);

// Fetch game image URL dan nama game dari DB
$query = "SELECT games_image, game_name FROM games WHERE id_game = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $game_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Game not found.");
}

$game = $result->fetch_assoc();
$imageUrl = $game['games_image'];
$gameName = $game['game_name']; 

// Validasi URL
if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
    die("Invalid image URL.");
}

// Dapatkan file extension dari URL
$fileExtension = strtolower(pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION));

// Check file extension
$contentType = 'application/octet-stream'; // Default tipe konten
// Set tipe konten berdasarkan file extensionnya
switch ($fileExtension) {
    case 'jpg':
    case 'jpeg':
        $contentType = 'image/jpeg';
        break;
    case 'png':
        $contentType = 'image/png';
        break;
    case 'gif':
        $contentType = 'image/gif';
        break;
    case 'bmp':
        $contentType = 'image/bmp';
        break;
    default:
        die("Unsupported file type.");
}

// Disable SSL option supaya tetap bisa mengunduh file
$options = [
    "ssl" => [
        "verify_peer" => false,
        "verify_peer_name" => false,
        "allow_self_signed" => true,
    ]
];
$context = stream_context_create($options);

// Fetch konten file dari URL
$fileContent = file_get_contents($imageUrl, false, $context);
if ($fileContent === false) {
    die("Failed to retrieve the image.");
}

// Menyajikan file untuk didownload
header('Content-Description: File Transfer');
header("Content-Type: $contentType");
header('Content-Disposition: attachment; filename="' . $gameName . '.' . $fileExtension . '"'); // Menggunakan nama game sebagai nama file
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . strlen($fileContent));
echo $fileContent;
exit;
?>
