<?php


require 'PHPMailer.php';
require 'SMTP.php';
require 'Exception.php';
require 'generate_pdf.php'; 
require 'send_email.php';

$servername = 'mysql-d5a1f3e-ymishra502-1c9c.e.aivencloud.com';      
$username = 'avnadmin';       
$password = 'AVNS_syB-8FeCZFNJ3mLjV74';         
$dbname = 'defaultdb'; 


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $client_name = $_POST['client_name'];
    $invoice_number = $_POST['invoice_number'];
    $invoice_date = $_POST['invoice_date'];
    $currency = $_POST['currency'];
    $items = $_POST['items']; 
    $tax_rate = $_POST['tax_rate'];
    $discount_rate = $_POST['discount_rate'];
    $invoice_total = 0;

   
    foreach ($items as $item) {
        $item_total = ($item['price'] * $item['quantity']) - ($item['price'] * $item['discount'] / 100);
        $invoice_total += $item_total;
    }
 
    $invoice_total += $invoice_total * ($tax_rate / 100); 
    $invoice_total -= $invoice_total * ($discount_rate / 100); 

    generateInvoicePDF($client_name, $invoice_number, $invoice_date, $items, $currency, $tax_rate, $discount_rate, $invoice_total);

  
    $sql = "INSERT INTO invoices (client_name, invoice_number, invoice_date, currency, tax_rate, discount_rate, invoice_total) 
            VALUES ('$client_name', '$invoice_number', '$invoice_date', '$currency', '$tax_rate', '$discount_rate', '$invoice_total')";

    if ($conn->query($sql) === TRUE) {
        // Get the last inserted invoice id
        $invoice_id = $conn->insert_id;

        // Insert invoice items into invoice_items table
        foreach ($items as $item) {
            $description = $item['description'];
            $quantity = (int)$item['quantity'];
            $price = (float)$item['price'];
            $discount = (float)$item['discount'];

            // Ensure proper escaping to prevent SQL injection
            $description = $conn->real_escape_string($description);
            $quantity = (int)$quantity;
            $price = (float)$price;
            $discount = (float)$discount;

            $sql_item = "INSERT INTO invoice_items (invoice_id, description, quantity, price, discount_rate) 
                         VALUES ('$invoice_id', '$description', '$quantity', '$price', '$discount')";
            $conn->query($sql_item);
        }

        echo "Invoice generated and saved successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }

    if (isset($_POST['send_email'])) {
        sendEmail($client_name, $invoice_number, $invoice_date, $currency, $tax_rate, $discount_rate, $items, $invoice_total);
    }
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Generator</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 font-sans">

    <div class="max-w-4xl mx-auto p-8 bg-white shadow-lg rounded-lg mt-10">
        <h1 class="text-3xl font-semibold text-center mb-6">Invoice Generator</h1>
        
        <form action="/generate_invoice" method="POST">
            <!-- Client Info Section -->
            <div class="space-y-4">
                <div>
                    <label for="client_name" class="block font-semibold">Client Name:</label>
                    <input type="text" name="client_name" required class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label for="invoice_number" class="block font-semibold">Invoice Number:</label>
                    <input type="text" name="invoice_number" required class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label for="invoice_date" class="block font-semibold">Invoice Date:</label>
                    <input type="date" name="invoice_date" required class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label for="currency" class="block font-semibold">Currency:</label>
                    <select name="currency" class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="INR">INR</option>
                        <option value="USD">USD</option>
                        <option value="EUR">EUR</option>
                        <option value="GBP">GBP</option>
                    </select>
                </div>
              
            </div>

            <!-- Items Section -->
            <h3 class="text-2xl font-semibold mt-6">Items</h3>
            <div id="items-container" class="space-y-4 mt-4">
                <div class="item flex space-x-4 border-b border-gray-300 pb-4">
                    <div class="w-1/3">
                        <label class="block font-semibold">Item Description:</label>
                        <input type="text" name="items[0][description]" required class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="w-1/6">
                        <label class="block font-semibold">Quantity:</label>
                        <input type="number" name="items[0][quantity]" required class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="w-1/6">
                        <label class="block font-semibold">Price:</label>
                        <input type="number" name="items[0][price]" step="0.01" required class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="w-1/6">
                        <label class="block font-semibold">Discount:</label>
                        <input type="number" name="items[0][discount]" step="0.01" value="0" class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="w-1/12">
                    <label class="block font-semibold">Remove:</label>
                        <button type="button" class="text-red-500 hover:text-red-700 bg-red-100 hover:bg-red-200 p-2 rounded-md" onclick="removeItem(this)">
                            Delete
                        </button>
                    </div>
                </div>
            </div>

            <button type="button" id="add-item" class="w-full bg-green-500 text-white p-3 mt-4 rounded-md hover:bg-green-600 transition">Add More Items</button>

            <!-- Tax, Discount Rate Section -->
            <div class="space-y-4 mt-6">
                <div>
                    <label for="tax_rate" class="block font-semibold">Tax Rate (%):</label>
                    <input type="number" name="tax_rate" step="0.01" value="10" class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label for="discount_rate" class="block font-semibold">Invoice Discount (%):</label>
                    <input type="number" name="discount_rate" step="0.01" value="0" class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex space-x-4 mt-8">
                <button type="submit" class="w-full bg-blue-500 text-white p-3 rounded-md hover:bg-blue-600 transition">Generate Invoice</button>
                <button type="submit" name="send_email" class="w-full bg-blue-500 text-white p-3 rounded-md hover:bg-blue-600 transition">Send Invoice via Email</button>
            </div>
        </form>
    </div>

    <script>
        // Add item dynamically
        let itemIndex = 1;
        document.getElementById('add-item').addEventListener('click', function () {
            const container = document.getElementById('items-container');
            const newItem = document.createElement('div');
            newItem.className = 'item flex space-x-4 border-b border-gray-300 pb-4';
            newItem.innerHTML = `
                <div class="w-1/3">
                    <label class="block font-semibold">Item Description:</label>
                    <input type="text" name="items[${itemIndex}][description]" required class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="w-1/6">
                    <label class="block font-semibold">Quantity:</label>
                    <input type="number" name="items[${itemIndex}][quantity]" required class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="w-1/6">
                    <label class="block font-semibold">Price:</label>
                    <input type="number" name="items[${itemIndex}][price]" step="0.01" required class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="w-1/6">
                    <label class="block font-semibold">Discount:</label>
                    <input type="number" name="items[${itemIndex}][discount]" step="0.01" value="0" class="w-full p-3 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="w-1/12">
                    <button type="button" class="text-red-500 hover:text-red-700 bg-red-100 hover:bg-red-200 p-2 rounded-md" onclick="removeItem(this)">
                        Delete
                    </button>
                </div>
            `;
            container.appendChild(newItem);
            itemIndex++;
        });

        // Remove item
        function removeItem(button) {
            button.closest('.item').remove();
        }
    </script>
</body>

</html>
