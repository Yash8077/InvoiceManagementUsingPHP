<?php
session_start();

// Redirect if not logged in
if (!(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) && 
    !(isset($_COOKIE['loggedin']) && $_COOKIE['loggedin'] == 'true')) {

    // If not logged in, redirect to login page
    header('Location: /');
    exit;
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body class="bg-gray-100 font-sans min-h-screen flex">

    <aside class="w-64 bg-blue-900 text-white flex-shrink-0 h-screen sticky top-0 p-5 flex flex-col justify-between">
        <div>
        <h2 class="text-2xl font-semibold mb-8">Invoice Generator</h2>
            <nav class="space-y-4">
                <a href="/dashboard" class="flex items-center px-3 py-2 hover:bg-blue-800 rounded-md transition bg-blue-700 text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h7v7h-7z" />
                    </svg>
                    Dashboard
                </a>

                <a href="/manage_clients" class="flex items-center px-3 py-2 hover:bg-blue-800 rounded-md transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Manage Clients
                </a>
                <a href="/search_json" class="flex items-center px-3 py-2 hover:bg-blue-800 rounded-md transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12h14M7 8h14m-7 8h7" />
                    </svg>
                    Search Invoices
                </a>
                <a href="/view_reports" class="flex items-center px-3 py-2 hover:bg-blue-800 rounded-md transition">
                     <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" viewBox="0 0 24 24" fill="currentColor">
                         <path fill-rule="evenodd" d="M3 3h18a1 1 0 011 1v16a1 1 0 01-1 1H3a1 1 0 01-1-1V4a1 1 0 011-1zM5 17h2V9H5v8zm4 0h2V5H9v12zm4 0h2V7h-2v10zm4 0h2V3h-2v14z" clip-rule="evenodd" />
                    </svg>
                     View Reports
                </a>
            </nav>
        </div>
      
        <a href="/logout" class="flex items-center px-3 py-2 bg-red-600 hover:bg-red-700 rounded-md transition mt-auto">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7" />
            </svg>
            Logout
        </a>
    </aside>

    <main class="flex-1 p-8">
        <div class="max-w-6xl mx-auto p-8 bg-white shadow-lg rounded-lg">
            <h1 class="text-3xl font-semibold text-center mb-6">Welcome to Invoice System</h1>
            <div class="flex space-x-4 mb-6">
                <a href="/generate_invoice" class="block flex-1 text-center bg-blue-600 text-white py-2 rounded-md hover:bg-blue-700 transition">
                    Generate an Invoice
                </a>
                <a href="/search_json" class="block flex-1 text-center bg-green-600 text-white py-2 rounded-md hover:bg-green-700 transition">
                    Search an Invoice
                </a>
            </div>
            <h2 class="text-2xl font-semibold">Recent Invoices</h2>
            <div class="mt-4 overflow-x-auto">
                <table class="min-w-full bg-white border border-gray-300 rounded-lg shadow-sm">
                    <thead>
                        <tr class="bg-gray-200 text-gray-600 uppercase text-sm leading-normal">
                            <th class="py-3 px-6 text-left">Invoice Number</th>
                            <th class="py-3 px-6 text-left">Client Name</th>
                            <th class="py-3 px-6 text-left">Date</th>
                            <th class="py-3 px-6 text-left">Currency</th>
                            <th class="py-3 px-6 text-left">Total Amount</th>
                        </tr>
                    </thead>
                    <tbody class="text-gray-700 text-sm font-light">
                        <?php
                    
                    $servername = 'mysql-d5a1f3e-ymishra502-1c9c.e.aivencloud.com';      
                    $username = 'avnadmin';       
                    $password = 'AVNS_syB-8FeCZFNJ3mLjV74';         
                    $dbname = 'defaultdb'; 
                        // Create connection
                        define('server', 'mysql-d5a1f3e-ymishra502-1c9c.e.aivencloud.com'); // Corrected the server definition
define('port', '18929');
define('user', 'avnadmin'); // Database username
define('password', 'AVNS_syB-8FeCZFNJ3mLjV74'); // Database password
define('database', 'defaultdb'); // Database name
define('ca_cert', __DIR__ . '/ca.pem'); // Correct the path to your CA certificate
// Initialize the MySQL connection
$conn = mysqli_init();

// Enable SSL (optional, only if you're using SSL/TLS)
mysqli_ssl_set($conn, NULL, NULL, ca_cert, NULL, NULL);

// Connect to the database
if (!mysqli_real_connect($conn, server, user, password, database, port)) {
    die("Error: " . mysqli_connect_error());
}


              
                        $sql = "SELECT * FROM invoices ORDER BY invoice_date DESC LIMIT 5";
                        $result = $conn->query($sql);

                        if ($result->num_rows > 0) {
                    
                            while ($invoice = $result->fetch_assoc()) {
                                echo "<tr class='border-b border-gray-300 hover:bg-gray-100'>
                                        <td class='py-3 px-6'>{$invoice['invoice_number']}</td>
                                        <td class='py-3 px-6'>{$invoice['client_name']}</td>
                                        <td class='py-3 px-6'>{$invoice['invoice_date']}</td>
                                        <td class='py-3 px-6'>{$invoice['currency']}</td>
                                        <td class='py-3 px-6'>{$invoice['invoice_total']}</td>
                                    </tr>";
                            }
                        } else {
                            echo "<tr><td colspan='5' class='py-4 text-center text-gray-500'>No recent invoices found.</td></tr>";
                        }

               
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>

</html>
