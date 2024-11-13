<?php
// Start session
session_start();

// Check if the action parameter is set in the URL and it's 'logout'
if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    // Destroy the session and expire the cookie
    session_unset(); // Clears all session variables
    session_destroy(); // Destroys the session
    setcookie('loggedin', '', time() - 3600, '/'); // Expire cookie immediately

    // Redirect to login page
    header('Location: /');  // Adjust the URL if needed (login page URL)
    exit;  // Stop further execution of the script
}
?>
