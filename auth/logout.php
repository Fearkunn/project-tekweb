<?php
session_start();
session_unset();  // Menghapus semua session
session_destroy();  // Menghancurkan session

// Redirect ke halaman login setelah logout
header("Location: ../auth/login.php");
exit();
?>