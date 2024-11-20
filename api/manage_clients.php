<?php
session_start();
if (!(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) && 
    !(isset($_COOKIE['loggedin']) && $_COOKIE['loggedin'] == 'true')) {

    // If not logged in, redirect to login page
    header('Location: /');
    exit;
}

$servername = 'mysql-d5a1f3e-ymishra502-1c9c.e.aivencloud.com';      
$username = 'avnadmin';       
$password = 'AVNS_syB-8FeCZFNJ3mLjV74';         
$dbname = 'defaultdb'; 

$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$sortQuery = "ORDER BY client_name ASC"; 
if (isset($_GET['sort_by'])) {
    $sortBy = $_GET['sort_by'];
    
    if ($sortBy == 'name_desc') {
        $sortQuery = "ORDER BY client_name DESC"; 
    }
}


$sql = "SELECT client_name, COUNT(id) AS invoice_count 
        FROM invoices 
        GROUP BY client_name 
        $sortQuery";

$result = $conn->query($sql);


$clients = [];
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $clients[] = $row;
    }
} else {
    $clients = [];
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Clients</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 font-sans min-h-screen flex">
    <!-- Sidebar -->
    <aside class="w-64 bg-blue-900 text-white flex-shrink-0 h-screen sticky top-0 p-5 flex flex-col justify-between">
        <div>
            <h2 class="text-2xl font-semibold mb-8">Invoice Generator</h2>
            <nav class="space-y-4">
                <a href="/dashboard" class="flex items-center px-3 py-2 hover:bg-blue-800 rounded-md transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h7v7h-7z" />
                    </svg>
                    Dashboard
                </a>

                <a href="/manage_clients" class="flex items-center px-3 py-2 hover:bg-blue-800 rounded-md transition bg-blue-700 text-white">
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
        <a href="/logout" class="flex items-center px-3 py-2 bg-red-600 hover:bg-red-700 rounded-md transition mt-auto">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7" />
            </svg>
            Logout
        </a>
    </aside>

    <!-- Main Content -->
    <div class="flex-1 p-8">
        <div class="max-w-6xl mx-auto bg-white shadow-lg rounded-lg">
            <h1 class="text-3xl font-semibold text-center mb-6">Manage Clients</h1>

            <!-- Sort Options -->
            <div class="flex justify-between mb-6">
                <form method="GET" class="flex space-x-4">
                    <select name="sort_by" class="px-4 py-2 rounded-md border">
                        <option value="name_asc" <?php echo (isset($_GET['sort_by']) && $_GET['sort_by'] == 'name_asc') ? 'selected' : ''; ?>>Client Name (A-Z)</option>
                        <option value="name_desc" <?php echo (isset($_GET['sort_by']) && $_GET['sort_by'] == 'name_desc') ? 'selected' : ''; ?>>Client Name (Z-A)</option>
                    </select>
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md">Sort</button>
                </form>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6 p-6">
                <?php
                // Display each client as a card
                if (!empty($clients)) {
                    foreach ($clients as $client) {
                        echo "<div class='bg-gray-50 p-6 rounded-lg shadow-lg'>";
                        echo "<h2 class='text-xl font-semibold text-blue-900'>" . htmlspecialchars($client['client_name']) . "</h2>";
                        echo "<p class='text-gray-700'>Invoices: " . htmlspecialchars($client['invoice_count']) . "</p>";
                        echo "<a href='/view_invoices?client=" . urlencode($client['client_name']) . "' class='text-white bg-blue-600 px-4 py-2 rounded mt-3 block text-center hover:bg-blue-700 transition'>View Invoices</a>";
                        echo "</div>";
                    }
                } else {
                    echo "<p class='text-center text-gray-500'>No clients found.</p>";
                }
                ?>
            </div>
        </div>
    </div>
</body>

</html>
