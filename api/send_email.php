<?php
// Include PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendEmail($client_name, $invoice_number, $currency,$invoice_date) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; 
        $mail->SMTPAuth = true;
        $mail->Username = 'adarshpathan1970@gmail.com'; // Your email address
        $mail->Password = 'hwss jgkc xeyj ofmj'; // Your email password
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('adarshpathan1970@gmail.com', 'Invoice Generator');
        $mail->addAddress('ymishra502@gmail.com', $client_name); // Add recipient email address

        $mail->isHTML(true);
        $mail->Subject = 'Invoice ' . $invoice_number;
        $mail->Body    = "Invoice for client $client_name. Amount: $currency. Invoice Number: $invoice_number.";
        $mail->addAttachment('../invoice/'.$client_name.'_'.$invoice_number .'_'.$invoice_date.'.pdf');
        // $mail->addAttachment('../invoice/generated_invoice.pdf'); // Path to the generated PDF

        $mail->send();
        echo 'Email has been sent';
    } catch (Exception $e) {
        echo "Error: {$mail->ErrorInfo}";
    }
}
?>
