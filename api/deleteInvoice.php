<?php

$servername = 'mysql-d5a1f3e-ymishra502-1c9c.e.aivencloud.com';      
$username = 'avnadmin';       
$password = 'AVNS_syB-8FeCZFNJ3mLjV74';         
$dbname = 'defaultdb'; 

$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (isset($_POST['delete'])) {
   
    $invoiceNumber = $_POST['invoice_number'];
    $clientName = $_POST['client_name'];
    $invoiceDate = $_POST['invoice_date'];

    $pdfFile = '../invoice/' . $clientName . '_' . $invoiceNumber . '_' . $invoiceDate . '.pdf';

    if (file_exists($pdfFile)) {
        unlink($pdfFile); 
    }

    $sql = "DELETE FROM invoices WHERE invoice_number = ? AND client_name = ? AND invoice_date = ?";

 
    if ($stmt = $conn->prepare($sql)) {
     
        $stmt->bind_param("sss", $invoiceNumber, $clientName, $invoiceDate);

        if ($stmt->execute()) {
           
            header('Location: search_mysql.php'); 
            exit();
        } else {
          
            echo "Error deleting invoice: " . $stmt->error;
        }

    
        $stmt->close();
    } else {
        echo "Error preparing the statement: " . $conn->error;
    }
}

$conn->close();
?>
