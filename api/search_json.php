<?php
session_start();

if (!(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) && 
    !(isset($_COOKIE['loggedin']) && $_COOKIE['loggedin'] == 'true')) {

    // If not logged in, redirect to login page
    header('Location: /');
    exit;
}
?>
<?php
// Function to search invoices from JSON
function searchInvoices($searchTerm) {
    $invoices = [];
    if (file_exists('invoices.json')) {
        $invoices = json_decode(file_get_contents('invoices.json'), true);
    }

    $results = [];
    foreach ($invoices as $invoice) {
        if (stripos($invoice['client_name'], $searchTerm) !== false || 
            stripos($invoice['invoice_number'], $searchTerm) !== false) {
            // Construct the dynamic PDF file path
            $invoice['pdf_file'] = './invoice/' . $invoice['client_name'] . '_' . $invoice['invoice_number'] . '_' . $invoice['invoice_date'] . '.pdf';
            $results[] = $invoice;
        }
    }

    return $results;
}

// Function to delete invoice from JSON and file system
function deleteInvoice($invoiceNumber, $clientName) {
    // Read the JSON file and decode it
    $invoices = json_decode(file_get_contents('invoices.json'), true);
    $newInvoices = [];

    // Iterate through the invoices and filter out the one to be deleted
    foreach ($invoices as $invoice) {
        if ($invoice['invoice_number'] === $invoiceNumber && $invoice['client_name'] === $clientName) {
            // Remove the PDF file if it exists
            $pdfPath = './invoice/' . $clientName . '_' . $invoiceNumber . '_' . $invoice['invoice_date'] . '.pdf';
            if (file_exists($pdfPath)) {
                unlink($pdfPath); // Delete the PDF file
            }
        } else {
            // Keep other invoices
            $newInvoices[] = $invoice;
        }
    }

    // Save the new list of invoices back to the JSON file
    file_put_contents('invoices.json', json_encode($newInvoices, JSON_PRETTY_PRINT));
}

// Handle delete action
if (isset($_POST['delete'])) {
    $invoiceNumber = $_POST['invoice_number'];
    $clientName = $_POST['client_name'];
    deleteInvoice($invoiceNumber, $clientName);
    // Reload the page to reflect the deletion
    header("Location: search_json.php?search=" . $_POST['searchTerm']);
    exit();
}

$searchResults = [];
if (isset($_GET['search'])) {
    $searchTerm = $_GET['search'];
    $searchResults = searchInvoices($searchTerm);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Generator</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans flex">
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

                <a href="/manage_clients" class="flex items-center px-3 py-2 hover:bg-blue-800 rounded-md transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Manage Clients
                </a>
                <a href="/search_json" class="flex items-center px-3 py-2 hover:bg-blue-800 rounded-md transition bg-blue-700 text-white">
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


    <!-- Main Content Area -->
    <div class="flex-1 p-8">
        <div class="max-w-4xl mx-auto p-8 bg-white shadow-lg rounded-lg mt-10">
            <h1 class="text-3xl font-semibold text-center mb-6">Invoice Generator</h1>
            <h2 class="text-2xl font-semibold mt-10">Search Invoices</h2>
            <form method="GET" action="search_json.php" class="mt-4">
                <div class="flex space-x-4">
                    <input type="text" name="search" placeholder="Search by client name or invoice number" value="<?= isset($searchTerm) ? $searchTerm : '' ?>" class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <button type="submit" class="bg-blue-500 text-white p-3 rounded-md hover:bg-blue-600 transition">Search</button>
                </div>
            </form>

            <?php if (count($searchResults) > 0): ?>
                <h3 class="text-xl font-semibold mt-6">Search Results:</h3>
                <ul class="mt-4 space-y-4">
                    <?php foreach ($searchResults as $result): ?>
                        <li class="border-b border-gray-300 pb-4">
                            <strong>Invoice Number:</strong> <?= htmlspecialchars($result['invoice_number']) ?><br>
                            <strong>Client Name:</strong> <?= htmlspecialchars($result['client_name']) ?><br>
                            <strong>Invoice Date:</strong> <?= htmlspecialchars($result['invoice_date']) ?><br>
                            
                            <!-- Button to open PDF if it exists -->
                            <?php if (file_exists($result['pdf_file'])): ?>
                                <div class="mt-4 flex space-x-4">
                                    <!-- Open PDF button -->
                                    <a href="<?= $result['pdf_file'] ?>" target="_blank" class="w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600 transition text-center">
                                        Open PDF Invoice
                                    </a>
                                
                                    <!-- Delete button -->
                                    <form method="POST" action="search_json.php" class="w-full">
                                        <input type="hidden" name="invoice_number" value="<?= htmlspecialchars($result['invoice_number']) ?>">
                                        <input type="hidden" name="client_name" value="<?= htmlspecialchars($result['client_name']) ?>">
                                        <input type="hidden" name="searchTerm" value="<?= htmlspecialchars($searchTerm) ?>">
                                        <button type="submit" name="delete" class="w-full bg-red-500 text-white py-2 px-4 rounded-md hover:bg-red-600 transition text-center">
                                            Delete Invoice
                                        </button>
                                    </form>
                                </div>
                            <?php else: ?>
                                <span class="text-red-500">No PDF found</span>
                            <?php endif; ?>
                            
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
