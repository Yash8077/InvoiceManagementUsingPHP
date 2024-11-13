<?php
// Start session
session_start();

// Check if the user is logged in via session or cookie
if (!(isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) && !(isset($_COOKIE['loggedin']) && $_COOKIE['loggedin'] == true)) {
    // If not logged in, redirect to the login page
    header('Location: index.php');
    exit;
}

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
            $invoice['pdf_file'] = '../invoice/' . $invoice['client_name'] . '_' . $invoice['invoice_number'] . '_' . $invoice['invoice_date'] . '.pdf';
            $results[] = $invoice;
        }
    }

    return $results;
}

// Function to delete invoice from JSON and file system
function deleteInvoice($invoiceNumber, $clientName) {
    $invoices = json_decode(file_get_contents('invoices.json'), true);
    $newInvoices = [];

    foreach ($invoices as $invoice) {
        if ($invoice['invoice_number'] === $invoiceNumber && $invoice['client_name'] === $clientName) {
            $pdfPath = '../invoice/' . $clientName . '_' . $invoiceNumber . '_' . $invoice['invoice_date'] . '.pdf';
            if (file_exists($pdfPath)) {
                unlink($pdfPath);
            }
        } else {
            $newInvoices[] = $invoice;
        }
    }

    file_put_contents('invoices.json', json_encode($newInvoices, JSON_PRETTY_PRINT));
}

if (isset($_POST['delete'])) {
    $invoiceNumber = $_POST['invoice_number'];
    $clientName = $_POST['client_name'];
    deleteInvoice($invoiceNumber, $clientName);
    header("Location: view_invoices.php?client=" . urlencode($clientName));
    exit();
}

$clientName = isset($_GET['client']) ? $_GET['client'] : '';
$invoices = searchInvoices($clientName);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoices for <?php echo htmlspecialchars($clientName); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 font-sans min-h-screen flex">
    <!-- Sidebar -->
    <aside class="w-64 bg-blue-900 text-white flex-shrink-0 min-h-screen p-5 flex flex-col justify-between">
        <div>
        <h2 class="text-2xl font-semibold mb-8">Invoice Generator</h2>
            <nav class="space-y-4">
                <a href="dashboard.php" class="flex items-center px-3 py-2 hover:bg-blue-800 rounded-md transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h7v7H3zM14 3h7v7h-7zM3 14h7v7H3zM14 14h7v7h-7z" />
                    </svg>
                    Dashboard
                </a>

                <a href="manage_clients.php" class="flex items-center px-3 py-2 hover:bg-blue-800 rounded-md transition bg-blue-700 text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Manage Clients
                </a>
                <a href="search_json.php" class="flex items-center px-3 py-2 hover:bg-blue-800 rounded-md transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12h14M7 8h14m-7 8h7" />
                    </svg>
                    Search Invoices
                </a>
                <a href="view_reports.php" class="flex items-center px-3 py-2 hover:bg-blue-800 rounded-md transition">
                     <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" viewBox="0 0 24 24" fill="currentColor">
                         <path fill-rule="evenodd" d="M3 3h18a1 1 0 011 1v16a1 1 0 01-1 1H3a1 1 0 01-1-1V4a1 1 0 011-1zM5 17h2V9H5v8zm4 0h2V5H9v12zm4 0h2V7h-2v10zm4 0h2V3h-2v14z" clip-rule="evenodd" />
                    </svg>
                     View Reports
                </a>


            </nav>
        </div>
        
        <!-- Logout Button at the Bottom -->
        <a href="logout.php" class="flex items-center px-3 py-2 bg-red-600 hover:bg-red-700 rounded-md transition mt-auto">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7" />
            </svg>
            Logout
        </a>
    </aside>



    <!-- Main Content -->
    <div class="flex-1 p-8">
        <div class="max-w-4xl mx-auto bg-white shadow-lg rounded-lg">
            <h1 class="text-3xl font-semibold mb-6">Invoices for <?php echo htmlspecialchars($clientName); ?></h1>

            <?php if (!empty($invoices)) : ?>
                <div class="space-y-4">
                    <?php foreach ($invoices as $invoice) : ?>
                        <div class="p-6 bg-gray-50 rounded-lg shadow-md">
                            <p><strong>Invoice Number:</strong> <?php echo htmlspecialchars($invoice['invoice_number']); ?></p>
                            <p><strong>Date:</strong> <?php echo htmlspecialchars($invoice['invoice_date']); ?></p>
                            <p><strong>Total Amount:</strong> <?php echo htmlspecialchars($invoice['invoice_total']); ?></p>

                            <!-- Open PDF Button -->
                            <?php if (file_exists($invoice['pdf_file'])) : ?>
                                <a href="<?php echo htmlspecialchars($invoice['pdf_file']); ?>" target="_blank" class="text-white bg-blue-600 px-4 py-2 rounded inline-block mt-3 hover:bg-blue-700 transition">Open Invoice PDF</a>
                            <?php else : ?>
                                <p class="text-red-500 mt-3">PDF not found.</p>
                            <?php endif; ?>

                            <!-- Delete Invoice Button -->
                            <form action="view_invoices.php" method="post" class="inline-block mt-3">
                                <input type="hidden" name="invoice_number" value="<?php echo htmlspecialchars($invoice['invoice_number']); ?>">
                                <input type="hidden" name="client_name" value="<?php echo htmlspecialchars($clientName); ?>">
                                <input type="hidden" name="delete" value="1">
                                <button type="submit" class="text-white bg-red-600 px-4 py-2 rounded hover:bg-red-700 transition">Delete Invoice</button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <p class="text-center text-gray-500">No invoices found for this client.</p>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
