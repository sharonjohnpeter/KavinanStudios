<?php

header('Content-Type: application/json');

require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method"
    ]);
    exit;
}

// Get form data
$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$phone   = trim($_POST['phone'] ?? '');
$service = trim($_POST['service'] ?? '');
$message = trim($_POST['message'] ?? '');

// Validation
if (
    empty($name) ||
    empty($email) ||
    empty($phone) ||
    empty($service) ||
    empty($message)
) {
    echo json_encode([
        "success" => false,
        "message" => "All fields are required."
    ]);
    exit;
}

try {

    $mail = new PHPMailer(true);

    // SMTP Configuration
    $mail->isSMTP();
    $mail->Host       = 'smtp.gmail.com';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'yourgmail@gmail.com'; // Your Gmail
    $mail->Password   = 'your-app-password';   // Gmail App Password
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
    $mail->Port       = 587;

    // Sender
    $mail->setFrom('yourgmail@gmail.com', 'Kavinan Design Studio Website');

    // Reply to customer
    $mail->addReplyTo($email, $name);

    // Receive enquiry mail here
    $mail->addAddress('hello@kavinandesignstudio.com');

    $mail->isHTML(true);

    $mail->Subject = 'New Website Enquiry - Kavinan Design Studio';

    $mail->Body = "
    <h2>New Enquiry Received</h2>

    <table cellpadding='8' cellspacing='0' border='1' width='100%'>
        <tr>
            <td><strong>Name</strong></td>
            <td>{$name}</td>
        </tr>
        <tr>
            <td><strong>Email</strong></td>
            <td>{$email}</td>
        </tr>
        <tr>
            <td><strong>Phone</strong></td>
            <td>{$phone}</td>
        </tr>
        <tr>
            <td><strong>Service Required</strong></td>
            <td>{$service}</td>
        </tr>
        <tr>
            <td><strong>Project Details</strong></td>
            <td>" . nl2br(htmlspecialchars($message)) . "</td>
        </tr>
    </table>
    ";

    $mail->send();

    echo json_encode([
        "success" => true,
        "message" => "Enquiry sent successfully."
    ]);

} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "message" => "Mailer Error: " . $mail->ErrorInfo
    ]);
}