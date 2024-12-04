<?php
// Proses logout
session_start();
session_unset();
session_destroy();

// Hapus cookie 'remember_me_token' setelah logout
setcookie('remember_me_token', '', time() - 3600, '/', '', false, true);

// Redirect ke halaman login setelah logout
header("Location: login.php");
exit();

?>