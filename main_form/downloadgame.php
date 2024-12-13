<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

include('../db_connect/DatabaseConnection.php');

// Check if game_id is provided
if (!isset($_GET['game_id']) || empty($_GET['game_id'])) {
    die("Invalid request.");
}

$game_id = intval($_GET['game_id']);

// Fetch the game image URL and game name from the database
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
$gameName = $game['game_name']; // Get the game name

// Debugging: Check if the game name is correct
echo "Game Name: " . $gameName . "<br>";  // Make sure this shows

// Validate the URL
if (!filter_var($imageUrl, FILTER_VALIDATE_URL)) {
    die("Invalid image URL.");
}

// Get the file extension from the URL
$fileExtension = strtolower(pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION));

// Debugging: Check the file extension
echo "File Extension: " . $fileExtension . "<br>";

$contentType = 'application/octet-stream'; // Default content type

// Set the appropriate Content-Type based on the file extension
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

// Disable SSL verification (not recommended for production)
$options = [
    "ssl" => [
        "verify_peer" => false,
        "verify_peer_name" => false,
        "allow_self_signed" => true,  // Allow self-signed certificates
    ]
];
$context = stream_context_create($options);

// Fetch the file content from the URL
$fileContent = file_get_contents($imageUrl, false, $context);
if ($fileContent === false) {
    die("Failed to retrieve the image.");
}

// Serve the file for download
header('Content-Description: File Transfer');
header("Content-Type: $contentType");
header('Content-Disposition: attachment; filename="' . $gameName . '.' . $fileExtension . '"'); // Use game name as filename
header('Expires: 0');
header('Cache-Control: must-revalidate');
header('Pragma: public');
header('Content-Length: ' . strlen($fileContent));
echo $fileContent;
exit;
?>