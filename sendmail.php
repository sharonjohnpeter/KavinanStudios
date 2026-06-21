<?php
// ========== CORS HEADERS - MUST BE AT THE VERY TOP ==========
// Allow from any origin (for development)
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400'); // cache for 1 day
}

// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD'])) {
        header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
    }
    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");
    }
    exit(0);
}

// Enable error reporting for debugging (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// Set JSON header
header('Content-Type: application/json');

// Load Composer autoloader
require_once __DIR__ . '/vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// ========== FUNCTIONS ==========
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function validate_phone($phone) {
    return preg_match('/^[6-9]\d{9}$/', $phone);
}

function validate_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// ========== CHECK REQUEST METHOD ==========
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method. Please use POST.'
    ]);
    exit;
}

// ========== GET AND SANITIZE FORM DATA ==========
$name = isset($_POST['name']) ? sanitize_input($_POST['name']) : '';
$phone = isset($_POST['phone']) ? sanitize_input($_POST['phone']) : '';
$email = isset($_POST['email']) ? sanitize_input($_POST['email']) : '';
$service = isset($_POST['service']) ? sanitize_input($_POST['service']) : '';
$message = isset($_POST['message']) ? sanitize_input($_POST['message']) : '';

// ========== VALIDATION ==========
$errors = [];

if (empty($name)) {
    $errors[] = 'Name is required';
} elseif (strlen($name) < 2) {
    $errors[] = 'Name must be at least 2 characters';
}

if (empty($phone)) {
    $errors[] = 'Phone number is required';
} elseif (!validate_phone($phone)) {
    $errors[] = 'Please enter a valid 10-digit Indian mobile number';
}

if (empty($email)) {
    $errors[] = 'Email address is required';
} elseif (!validate_email($email)) {
    $errors[] = 'Please enter a valid email address';
}

if (empty($service)) {
    $errors[] = 'Please select a service';
}

if (empty($message)) {
    $errors[] = 'Project details are required';
} elseif (strlen($message) < 10) {
    $errors[] = 'Please provide more details about your project (minimum 10 characters)';
}

// If validation errors exist, return them
if (!empty($errors)) {
    echo json_encode([
        'success' => false,
        'message' => 'Please fix the following errors:',
        'errors' => $errors
    ]);
    exit;
}

// ========== SMTP CONFIGURATION ==========
// For Gmail:
// - Enable 2-Step Verification in your Google Account
// - Generate an App Password (Google Account > Security > App Passwords)

$smtp_config = [
    'host' => 'smtp.gmail.com',
    'port' => 587,
    'auth' => true,
    'username' => 'bhabuelectricals@gmail.com', // CHANGE THIS
    'password' => 'msurkhjwqafkudiw', // CHANGE THIS - 16 chars, NO spaces
    'encryption' => PHPMailer::ENCRYPTION_STARTTLS
];

// Recipient email - CHANGE THIS
$recipient_email = 'ar.srk@kavinandesignstudio.com';
$recipient_name = 'Kavinan Design Studio';

// ========== SEND EMAIL ==========
try {
    $mail = new PHPMailer(true);

    // Server settings
    $mail->isSMTP();
    $mail->Host       = $smtp_config['host'];
    $mail->SMTPAuth   = $smtp_config['auth'];
    $mail->Username   = $smtp_config['username'];
    $mail->Password   = $smtp_config['password'];
    $mail->SMTPSecure = $smtp_config['encryption'];
    $mail->Port       = $smtp_config['port'];
    
    $mail->SMTPDebug = 0;
    $mail->Timeout = 30;
    
    // Recipients
    $mail->setFrom($smtp_config['username'], 'Kavinan Design Studio Website');
    $mail->addAddress($recipient_email, $recipient_name);
    $mail->addReplyTo($email, $name);
    
    // Email content
    $mail->isHTML(true);
    $mail->Subject = "New Enquiry from {$name} - Kavinan Design Studio";
    
    // HTML Email Template
    $html_content = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>New Enquiry - Kavinan Design Studio</title>
        <style>
            body { font-family: "Inter", Arial, sans-serif; line-height: 1.6; color: #1a1a2e; background: #f5f5f5; margin: 0; padding: 20px; }
            .container { max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
            .header { background: linear-gradient(135deg, #0F6B4B, #1A4A3A); padding: 30px; text-align: center; }
            .header h1 { color: white; margin: 0; font-size: 24px; font-family: "Montserrat", sans-serif; }
            .header p { color: rgba(255,255,255,0.85); margin: 10px 0 0; font-family: "Caveat", cursive; font-size: 18px; }
            .header .tagline { color: #F26522; font-size: 14px; letter-spacing: 2px; margin-top: 5px; font-family: "Inter", sans-serif; text-transform: uppercase; }
            .content { padding: 30px; }
            .section { margin-bottom: 25px; border-bottom: 1px solid #EAE4DA; padding-bottom: 15px; }
            .section-title { font-size: 14px; font-weight: 700; color: #0F6B4B; margin-bottom: 10px; text-transform: uppercase; letter-spacing: 1px; }
            .section-content { font-size: 15px; color: #4a5568; }
            .badge { display: inline-block; background: #F26522; padding: 5px 15px; border-radius: 20px; font-size: 14px; color: white; font-weight: 600; }
            .footer { background: #1A4A3A; padding: 20px; text-align: center; color: rgba(255,255,255,0.7); font-size: 12px; }
            .footer a { color: #F26522; text-decoration: none; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h1>🏛️ New Enquiry Received</h1>
                <p>Kavinan Design Studio</p>
                <div class="tagline">Rooted with design. Built with vision.</div>
            </div>
            <div class="content">
                <div class="section">
                    <div class="section-title">Customer Information</div>
                    <p class="section-content">
                        <strong>Name:</strong> ' . htmlspecialchars($name) . '<br>
                        <strong>Phone:</strong> <a href="tel:+91' . htmlspecialchars($phone) . '" style="color: #0F6B4B;">+91 ' . htmlspecialchars($phone) . '</a><br>
                        <strong>Email:</strong> <a href="mailto:' . htmlspecialchars($email) . '" style="color: #0F6B4B;">' . htmlspecialchars($email) . '</a>
                    </p>
                </div>
                
                <div class="section">
                    <div class="section-title">Service Required</div>
                    <p class="section-content">
                        <span class="badge">' . htmlspecialchars($service) . '</span>
                    </p>
                </div>
                
                <div class="section">
                    <div class="section-title">Project Details</div>
                    <p class="section-content">' . nl2br(htmlspecialchars($message)) . '</p>
                </div>
                
                <div class="section">
                    <div class="section-title">Submission Details</div>
                    <p class="section-content">
                        <strong>Date & Time:</strong> ' . date('F j, Y, g:i a') . '<br>
                        <strong>IP Address:</strong> ' . ($_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? 'Unknown') . '
                    </p>
                </div>
            </div>
            <div class="footer">
                <p>© ' . date('Y') . ' Kavinan Design Studio. All rights reserved.</p>
                <p>Kumbakonam, Tamil Nadu, India</p>
            </div>
        </div>
    </body>
    </html>';
    
    $mail->Body = $html_content;
    $mail->AltBody = "New Enquiry from Kavinan Design Studio\n\n"
        . "Name: {$name}\nPhone: +91 {$phone}\nEmail: {$email}\n\n"
        . "Service: {$service}\n\nMessage:\n{$message}";
    
    $mail->send();
    
    // ========== SEND AUTO-RESPONSE ==========
    $auto_mail = new PHPMailer(true);
    $auto_mail->isSMTP();
    $auto_mail->Host       = $smtp_config['host'];
    $auto_mail->SMTPAuth   = $smtp_config['auth'];
    $auto_mail->Username   = $smtp_config['username'];
    $auto_mail->Password   = $smtp_config['password'];
    $auto_mail->SMTPSecure = $smtp_config['encryption'];
    $auto_mail->Port       = $smtp_config['port'];
    
    $auto_mail->setFrom($smtp_config['username'], 'Kavinan Design Studio');
    $auto_mail->addAddress($email, $name);
    $auto_mail->addReplyTo($smtp_config['username'], 'Kavinan Design Studio');
    
    $auto_mail->isHTML(true);
    $auto_mail->Subject = "Thank you for contacting Kavinan Design Studio";
    
    $auto_content = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Thank You - Kavinan Design Studio</title>
        <style>
            body { font-family: "Inter", Arial, sans-serif; line-height: 1.6; color: #1a1a2e; margin: 0; padding: 20px; background: #f5f5f5; }
            .container { max-width: 500px; margin: 0 auto; background: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
            .header { background: linear-gradient(135deg, #0F6B4B, #1A4A3A); padding: 30px; text-align: center; }
            .header h2 { color: white; margin: 0; font-family: "Montserrat", sans-serif; }
            .header p { color: rgba(255,255,255,0.85); font-family: "Caveat", cursive; font-size: 16px; }
            .content { padding: 30px; }
            .btn { display: inline-block; background: #F26522; color: white; padding: 12px 28px; border-radius: 40px; text-decoration: none; margin-top: 20px; font-weight: 600; }
            .btn:hover { background: #d9561c; }
            .footer { background: #EAE4DA; padding: 15px; text-align: center; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h2>🌿 Thank You, ' . htmlspecialchars($name) . '!</h2>
                <p>Rooted with design. Built with vision.</p>
            </div>
            <div class="content">
                <p>We have received your enquiry regarding <strong style="color: #0F6B4B;">' . htmlspecialchars($service) . '</strong>.</p>
                <p>Our team will review your requirements and get back to you within <strong>one business day</strong>.</p>
                <p style="text-align: center; font-size: 24px; font-weight: bold; color: #0F6B4B;">
                    <a href="tel:+919876543210" style="color: #0F6B4B; text-decoration: none;">+91 98765 43210</a>
                </p>
                <center>
                    <a href="https://wa.me/919876543210" class="btn">💬 Chat on WhatsApp</a>
                </center>
            </div>
            <div class="footer">
                <p><strong>Kavinan Design Studio</strong><br>Kumbakonam, Tamil Nadu, India</p>
            </div>
        </div>
    </body>
    </html>';
    
    $auto_mail->Body = $auto_content;
    $auto_mail->AltBody = "Thank you for contacting Kavinan Design Studio!\n\n"
        . "We received your enquiry regarding {$service}.\n"
        . "Our team will get back to you within one business day.\n"
        . "Call us: +91 98765 43210";
    
    $auto_mail->send();
    
    echo json_encode([
        'success' => true,
        'message' => 'Your enquiry has been sent successfully! We will contact you within one business day.'
    ]);
    
} catch (Exception $e) {
    error_log("Mailer Error: {$mail->ErrorInfo}");
    
    echo json_encode([
        'success' => false,
        'message' => 'Unable to send your enquiry at this time. Please call us directly at +91 98765 43210.'
    ]);
}
?>