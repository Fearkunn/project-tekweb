<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = ""; //isi nama db kalian sendiri
// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully";
// Close the connection
$conn->close();
?>
