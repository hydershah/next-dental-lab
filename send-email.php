<?php
// Configuration - Set your email address here
$to_email = "your-email@nextdentallab.com";
$subject_prefix = "New Lead from Website";

// Allow CORS for same-origin requests
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

// Get form data
$name = isset($_POST['name']) ? htmlspecialchars(strip_tags($_POST['name'])) : '';
$email = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_SANITIZE_EMAIL) : '';
$phone = isset($_POST['phone']) ? htmlspecialchars(strip_tags($_POST['phone'])) : '';
$practice = isset($_POST['practice']) ? htmlspecialchars(strip_tags($_POST['practice'])) : '';
$message = isset($_POST['message']) ? htmlspecialchars(strip_tags($_POST['message'])) : '';

// Validate required fields
if (empty($name) || empty($email) || empty($phone)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

// Build email content
$email_subject = "$subject_prefix: $name";
$email_body = "New lead from Next Dental Lab website:\n\n";
$email_body .= "Name: $name\n";
$email_body .= "Email: $email\n";
$email_body .= "Phone: $phone\n";
$email_body .= "Practice Name: $practice\n";
$email_body .= "Message: $message\n\n";
$email_body .= "---\nSubmitted on: " . date('Y-m-d H:i:s');

// Email headers
$headers = "From: $email\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

// Send email
if (mail($to_email, $email_subject, $email_body, $headers)) {
    echo json_encode(['success' => true, 'message' => 'Thank you! We will contact you soon.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to send email. Please call us instead.']);
}
?>
