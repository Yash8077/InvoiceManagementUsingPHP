
<?php
// MySQL Database credentials
require 'config.php';
// Function to search invoices from MySQL
function searchInvoices($searchTerm) {
    global $conn;
    $results = [];
    $sql = "SELECT * FROM invoices WHERE client_name LIKE ? OR invoice_number LIKE ?";
    $stmt = $conn->prepare($sql);
    $searchTerm = "%" . $searchTerm . "%"; 
    $stmt->bind_param('ss', $searchTerm, $searchTerm);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($invoice = $result->fetch_assoc()) {
        $invoice['pdf_file'] = '../invoice/' . $invoice['client_name'] . '_' . $invoice['invoice_number'] . '_' . $invoice['invoice_date'] . '.pdf';
        $results[] = $invoice;
    }

    return $results;
}

// Function to delete invoice from MySQL and file system
function deleteInvoice($invoiceNumber, $clientName) {
    global $conn;

    $sql = "SELECT pdf_file FROM invoices WHERE invoice_number = ? AND client_name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $invoiceNumber, $clientName);
    $stmt->execute();
    $result = $stmt->get_result();
    $invoice = $result->fetch_assoc();

    // Delete PDF file from server
    if ($invoice && file_exists($invoice['pdf_file'])) {
        unlink($invoice['pdf_file']);
    }

    // Delete invoice record from the database
    $sql = "DELETE FROM invoices WHERE invoice_number = ? AND client_name = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ss', $invoiceNumber, $clientName);
    $stmt->execute();
}

// Handle delete request
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
                <a href="index.php" class="flex items-center px-3 py-2 hover:bg-blue-800 rounded-md transition">
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
                                <a href="<?php echo htmlspecialchars($invoice['pdf_file']); ?>" target="_blank" class="text-white bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-md">
                                    View PDF
                                </a>
                            <?php endif; ?>

                            <!-- Delete Button -->
                            <form method="POST" class="inline-block mt-4">
                                <input type="hidden" name="invoice_number" value="<?php echo htmlspecialchars($invoice['invoice_number']); ?>">
                                <input type="hidden" name="client_name" value="<?php echo htmlspecialchars($invoice['client_name']); ?>">
                                <button type="submit" name="delete" class="text-white bg-red-600 hover:bg-red-700 px-4 py-2 rounded-md">
                                    Delete
                                </button>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else : ?>
                <p>No invoices found for this client.</p>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>

