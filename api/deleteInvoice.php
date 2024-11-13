<?php
if (isset($_POST['delete'])) {
    // Get the invoice details from the form
    $invoiceNumber = $_POST['invoice_number'];
    $clientName = $_POST['client_name'];
    $invoiceDate = $_POST['invoice_date'];

    // Path to the PDF file
    $pdfFile = '../invoice/' . $clientName . '_' . $invoiceNumber . '_' . $invoiceDate . '.pdf';

    // Delete the PDF file if it exists
    if (file_exists($pdfFile)) {
        unlink($pdfFile); // Delete the PDF file from the server
    }

    // Read the existing invoices from the JSON file
    $invoices = json_decode(file_get_contents('invoices.json'), true);

    // Find the index of the invoice to be deleted
    foreach ($invoices as $key => $invoice) {
        if ($invoice['invoice_number'] === $invoiceNumber && $invoice['client_name'] === $clientName && $invoice['invoice_date'] === $invoiceDate) {
            // Remove the invoice from the array
            unset($invoices[$key]);
            break;
        }
    }

    // Reindex the array after deletion
    $invoices = array_values($invoices);

    // Save the updated invoices back to the JSON file
    file_put_contents('invoices.json', json_encode($invoices, JSON_PRETTY_PRINT));

    // Redirect back to the invoice search page
    header('Location: search_json.php');
    exit();
}
?>
