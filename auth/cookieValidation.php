<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$username = 'Guest'; 
if (isset($_SESSION['username'])) {
    $username = $_SESSION['username'];
    $is_logged_in = true;  // User is logged in
} else {
    // If session is not set, check for the Remember Me token in cookies
    if (isset($_COOKIE['remember_me_token'])) {
        $token = base64_decode($_COOKIE['remember_me_token'], true);
        
        if ($token === false || substr_count($token, '|') !== 2) {
            error_log("Invalid token format");
            setcookie('remember_me_token', '', time() - 3600, "/", "", false, true);
            $is_logged_in = false;
        } else {
            list($user_id, $expiry, $signature) = explode('|', $token);
            
            if (time() > $expiry) {
                error_log("Token expired");
                setcookie('remember_me_token', '', time() - 3600, "/", "", false, true);
                $is_logged_in = false;
            } else {
                // Validate the token's signature
                $secret_key = 'your_secret_key';
                $valid_signature = hash_hmac('sha256', "$user_id|$expiry", $secret_key);
                
                if (hash_equals($valid_signature, $signature)) {
                    // Token is valid, log the user in

                    // Fetch the username from the database using user_id
                    $sql = "SELECT username FROM users WHERE id_user = ?";
                    $stmt = $conn->prepare($sql);
                    $stmt->bind_param("i", $user_id);  // Bind user_id as integer
                    $stmt->execute();
                    $stmt->bind_result($fetched_username);
                    
                    if ($stmt->fetch()) {
                        // Set session values
                        $_SESSION['user_id'] = $user_id;
                        $_SESSION['username'] = $fetched_username;  // Set the username from the database
                        $username = $fetched_username;  // Update $username with the value from the database
                        $is_logged_in = true;
                    } else {
                        // If user not found in DB, handle accordingly (e.g., logout)
                        error_log("User not found in the database");
                        $is_logged_in = false;
                    }
                    $stmt->close();
                } else {
                    error_log("Invalid token signature");
                    setcookie('remember_me_token', '', time() - 3600, "/", "", false, true);
                    $is_logged_in = false;
                }
            }
        }
    } else {
        // No cookie found, user is a guest
        $is_logged_in = false;
    }
}

?>