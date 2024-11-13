<?php
// Start session
session_start();
$_SESSION['loggedin'] = false;
// Destroy the session and expire the cookie
session_unset();
session_destroy();

// Expire the cookie by setting its expiration time to the past
setcookie('loggedin', 'false', time() - 3600, '/'); // Expire cookie immediately

// Redirect to login page
header('Location: /');  // Adjust the URL if needed
exit;
?>
