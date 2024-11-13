<?php
// Start session
session_start();

// Predefined credentials
define('USERNAME', 'admin');
define('PASSWORD', 'password123');

// Check if user is already logged in using cookies
if (isset($_COOKIE['loggedin']) && $_COOKIE['loggedin'] == true) {
    $_SESSION['loggedin'] = true;
    header('Location: dashboard.php');
    exit;
}*/ -->


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Invoice System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>

<body class="bg-gradient-to-r from-blue-500 to-purple-600 h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-lg w-full max-w-md">
        <div class="text-center mb-6">
            <h2 class="text-3xl font-semibold text-gray-800">Welcome Back</h2>
            <p class="text-gray-500">Please log in to continue</p>
        </div>
        
        <!-- Logout Button at the Bottom -->
        <a href="dashboard.php?action=logout" class="flex items-center px-3 py-2 bg-red-600 hover:bg-red-700 rounded-md transition mt-auto">
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
