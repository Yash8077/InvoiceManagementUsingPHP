<?php
session_start();

// Clear session data
session_unset();
session_destroy();

// Clear cookie
setcookie('loggedin', '', time() - 3600, '/'); // Expire immediately

// Redirect to login page
header('Location: /');
exit;
?>
