<?php
require_once('./fpdf/fpdf.php'); 

function generateInvoicePDF($client_name, $invoice_number, $invoice_date, $items, $currency, $tax_rate, $discount_rate,&$invoice_total) {

    $directory = './invoice/';  
    if (!is_dir($directory)) {
        mkdir($directory, 0777, true);
    }

    // Initialize FPDF
    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetFont('Arial', 'B', 16); 

    // Invoice Header
    $pdf->Cell(200, 10, 'Invoice', 0, 1, 'C');
    $pdf->Ln(10);

    // Invoice Details
    $pdf->SetFont('Arial', '', 12); 
    $pdf->Cell(50, 10, 'Invoice Number: ' . $invoice_number);
    $pdf->Ln(5);
    $pdf->Cell(50, 10, 'Invoice Date: ' . $invoice_date);
    $pdf->Ln(5);
    $pdf->Cell(50, 10, 'Client Name: ' . $client_name);
    $pdf->Ln(10);

    // Table Header
    $pdf->Cell(50, 10, 'Description', 1);
    $pdf->Cell(30, 10, 'Quantity', 1);
    $pdf->Cell(30, 10, 'Unit Price', 1);
    $pdf->Cell(30, 10, 'Discount', 1);
    $pdf->Cell(30, 10, 'Total', 1);
    $pdf->Ln();

    // Table Data
    $total = 0;
    foreach ($items as $item) {
        $item_total = ($item['quantity'] * $item['price']) - ($item['quantity'] * $item['price'] * ($item['discount'] / 100));
        $total += $item_total;

        $pdf->Cell(50, 10, $item['description'], 1);
        $pdf->Cell(30, 10, $item['quantity'], 1);
        $pdf->Cell(30, 10, $item['price'], 1);
        $pdf->Cell(30, 10, $item['discount'] . '%', 1);
        $pdf->Cell(30, 10, number_format($item_total, 2), 1);
        $pdf->Ln();
    }

    // Tax and Discount Calculation
    $subtotal = $total;
    $tax = $subtotal * ($tax_rate / 100);
    $discount = $subtotal * ($discount_rate / 100);
    $grand_total = $subtotal + $tax - $discount;
    $invoice_total=$grand_total;
    // Summary
    $pdf->Ln(10);
    $pdf->Cell(150, 10, 'Subtotal', 0);
    $pdf->Cell(30, 10, number_format($subtotal, 2), 0, 1, 'R');
    $pdf->Cell(150, 10, 'Tax (' . $tax_rate . '%)', 0);
    $pdf->Cell(30, 10, number_format($tax, 2), 0, 1, 'R');
    $pdf->Cell(150, 10, 'Discount (' . $discount_rate . '%)', 0);
    $pdf->Cell(30, 10, number_format($discount, 2), 0, 1, 'R');
    $pdf->Cell(150, 10, 'Grand Total', 0);
    $pdf->Cell(30, 10, number_format($grand_total, 2), 0, 1, 'R');
    $pdf->Output('F', $directory . $client_name.'_'.$invoice_number .'_'.$invoice_date.'.pdf');
}

?>
