<?php
// Start session
session_start();

// Predefined credentials
define('USERNAME', 'admin');
define('PASSWORD', 'password123');

// Check if user is already logged in using either session or cookie
if ((isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) || 
    (isset($_COOKIE['loggedin']) && $_COOKIE['loggedin'] == true)) {

    // Redirect to dashboard if logged in
    header('Location: /dashboard');
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Authenticate user
    if ($username === USERNAME && $password === PASSWORD) {
        // Set session and cookie for 7 days
        $_SESSION['loggedin'] = true;
        setcookie('loggedin', 'true', time() + (7 * 24 * 60 * 60), '/'); // 7 days expiration
        
        header('Location: /dashboard');
        exit;
    } else {
        $error = 'Invalid username or password';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LandmarkLogin - Invoice System</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-r from-blue-500 to-purple-600 h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <div class="text-center mb-6">
            <h2 class="text-3xl font-semibold text-gray-800">Welcome Back</h2>
            <p class="text-gray-500">Please log in to continue</p>
        </div>

        <?php if (!empty($error)) : ?>
            <p class="text-red-600 text-center mb-4 font-semibold"><?= $error ?></p>
        <?php endif; ?>

        <form action="/" method="post" class="space-y-6">
            <div class="relative">
                <label for="username" class="sr-only">Username</label>
                <input type="text" name="username" id="username" placeholder="Username" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                    required>
            </div>

            <div class="relative">
                <label for="password" class="sr-only">Password</label>
                <input type="password" name="password" id="password" placeholder="Password" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                    required>
            </div>

            <button type="submit" 
                class="w-full py-3 rounded-md bg-blue-600 hover:bg-blue-700 text-white font-semibold transition duration-300 transform hover:scale-105">
                Login
            </button>
        </form>

        <div class="mt-6 text-center">
            <p class="text-gray-600">Dont have an account? 
                <a href="#" class="text-blue-500 hover:text-blue-700 font-semibold">Sign up</a>
            </p>
        </div>
    </div>
</body>
</html>
