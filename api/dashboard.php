<?php
// Start session
session_start();

if (isset($_GET['action']) && $_GET['action'] == 'logout') {
    // Destroy the session and expire the cookie
    session_unset(); // Clears all session variables
    session_destroy(); // Destroys the session
    setcookie('loggedin', '', time() - 3600, '/'); // Expire cookie immediately

    // Redirect to login page
    header('Location: /l');  // Adjust the URL to point to your login page
    exit;  // Stop further execution of the script
}

// Check login status based on session or cookie
if (!(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) && 
    !(isset($_COOKIE['loggedin']) && $_COOKIE['loggedin'] == 'true')) {

    // If not logged in, redirect to login page
    if ($_SERVER['REQUEST_URI'] != '/') {
        header('Location: /');
        exit;
    }
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
    <!-- Sidebar -->
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
        
        <!-- Logout Button at the Bottom -->
        <a href="?action=logout" class="flex items-center px-3 py-2 bg-red-600 hover:bg-red-700 rounded-md transition mt-auto">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7" />
            </svg>
            Logout
        </a>
    </aside>


    <!-- Main Content -->
    <main class="flex-1 p-8">
        <div class="max-w-6xl mx-auto bg-white shadow-lg rounded-lg overflow-hidden">
            <div class="p-8">
                <h1 class="text-4xl font-bold text-center text-blue-900 mb-6">Welcome to the Invoice System</h1>
                <div class="flex space-x-4 mb-8 justify-center">
                    <a href="generate_invoice.php" class="block flex-1 text-center bg-blue-600 text-white py-3 px-5 rounded-md hover:bg-blue-700 transition shadow-md transform hover:scale-105">
                        Generate an Invoice
                    </a>
                    <a href="search_json.php" class="block flex-1 text-center bg-green-600 text-white py-3 px-5 rounded-md hover:bg-green-700 transition shadow-md transform hover:scale-105">
                        Search an Invoice
                    </a>
                </div>
                
                <h2 class="text-2xl font-semibold text-blue-800 mb-4">Recent Invoices</h2>
                <div class="overflow-x-auto rounded-lg">
                    <table class="min-w-full bg-white border border-gray-300 rounded-lg shadow-md">
                        <thead>
                            <tr class="bg-gray-200 text-gray-700 uppercase text-sm leading-normal">
                                <th class="py-4 px-6 text-left font-semibold">Invoice Number</th>
                                <th class="py-4 px-6 text-left font-semibold">Client Name</th>
                                <th class="py-4 px-6 text-left font-semibold">Date</th>
                                <th class="py-4 px-6 text-left font-semibold">Currency</th>
                                <th class="py-4 px-6 text-left font-semibold">Total Amount</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-600 text-sm font-light">
                            <?php
                            // Load invoices from JSON file
                            $invoices = [];
                            if (file_exists('invoices.json')) {
                                $invoices = json_decode(file_get_contents('invoices.json'), true);
                            }

                            // Display recent invoices, up to the 5 most recent ones
                            $recentInvoices = array_slice(array_reverse($invoices), 0, 5);
                            if (!empty($recentInvoices)) {
                                foreach ($recentInvoices as $invoice) {
                                    echo "<tr class='border-b border-gray-300 hover:bg-gray-100 transition'>
                                            <td class='py-4 px-6'>{$invoice['invoice_number']}</td>
                                            <td class='py-4 px-6'>{$invoice['client_name']}</td>
                                            <td class='py-4 px-6'>{$invoice['invoice_date']}</td>
                                            <td class='py-4 px-6'>{$invoice['currency']}</td>
                                            <td class='py-4 px-6'>{$invoice['total_amount']}</td>
                                          </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' class='py-4 px-6 text-center text-gray-500'>No invoices found.</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
