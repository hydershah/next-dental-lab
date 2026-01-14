<?php
// Configuration - Set your email address here
$to_email = "your-email@nextdentallab.com";
$subject_prefix = "New Practice Registration";

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

// Helper function to sanitize input
function sanitize($input) {
    return isset($input) ? htmlspecialchars(strip_tags(trim($input))) : '';
}

// Get form data - Contact Information
$email = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_SANITIZE_EMAIL) : '';
$doctor_first_name = sanitize($_POST['doctor_first_name'] ?? '');
$doctor_last_name = sanitize($_POST['doctor_last_name'] ?? '');
$company_name = sanitize($_POST['company_name'] ?? '');
$primary_contact = sanitize($_POST['primary_contact'] ?? '');
$phone = sanitize($_POST['phone'] ?? '');
$mobile_phone = sanitize($_POST['mobile_phone'] ?? '');

// Get form data - Address Information
$street_address_1 = sanitize($_POST['street_address_1'] ?? '');
$street_address_2 = sanitize($_POST['street_address_2'] ?? '');
$city = sanitize($_POST['city'] ?? '');
$state = sanitize($_POST['state'] ?? '');
$postal_code = sanitize($_POST['postal_code'] ?? '');
$county = sanitize($_POST['county'] ?? '');

// Get form data - Professional Information
$license_number = sanitize($_POST['license_number'] ?? '');
$npi_number = sanitize($_POST['npi_number'] ?? '');
$practice_type = sanitize($_POST['practice_type'] ?? '');
$referral_source = sanitize($_POST['referral_source'] ?? '');

// Get form data - Consent
$privacy_policy = isset($_POST['privacy_policy']) ? 'Yes' : 'No';
$marketing_consent = isset($_POST['marketing_consent']) ? 'Yes' : 'No';

// Validate required fields
$required_fields = [
    'Email' => $email,
    'Doctor First Name' => $doctor_first_name,
    'Doctor Last Name' => $doctor_last_name,
    'Company Name' => $company_name,
    'Phone' => $phone,
    'Mobile Phone' => $mobile_phone,
    'Street Address' => $street_address_1,
    'City' => $city,
    'State' => $state,
    'Postal Code' => $postal_code,
    'County' => $county,
    'License Number' => $license_number,
    'Practice Type' => $practice_type,
    'Referral Source' => $referral_source
];

$missing_fields = [];
foreach ($required_fields as $field_name => $field_value) {
    if (empty($field_value)) {
        $missing_fields[] = $field_name;
    }
}

if (!empty($missing_fields)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields: ' . implode(', ', $missing_fields)]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

if ($privacy_policy !== 'Yes') {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'You must agree to the Privacy Policy & Terms']);
    exit;
}

// Format practice type for display
$practice_type_labels = [
    'general_dentist' => 'General Dentist',
    'prosthodontist' => 'Prosthodontist',
    'oral_surgeon' => 'Oral Surgeon',
    'periodontist' => 'Periodontist',
    'endodontist' => 'Endodontist',
    'orthodontist' => 'Orthodontist',
    'pediatric_dentist' => 'Pediatric Dentist',
    'dental_group' => 'Dental Group/DSO',
    'other' => 'Other'
];

$referral_source_labels = [
    'google_search' => 'Google Search',
    'facebook' => 'Facebook',
    'instagram' => 'Instagram',
    'linkedin' => 'LinkedIn',
    'referral' => 'Referral from Colleague',
    'dental_conference' => 'Dental Conference/Trade Show',
    'dental_publication' => 'Dental Publication',
    'email_marketing' => 'Email Marketing',
    'other' => 'Other'
];

$practice_type_display = $practice_type_labels[$practice_type] ?? $practice_type;
$referral_source_display = $referral_source_labels[$referral_source] ?? $referral_source;

// Build email content
$doctor_name = "$doctor_first_name $doctor_last_name";
$email_subject = "$subject_prefix: Dr. $doctor_name - $company_name";

$email_body = "═══════════════════════════════════════════════════════════\n";
$email_body .= "           NEW DENTAL PRACTICE REGISTRATION\n";
$email_body .= "═══════════════════════════════════════════════════════════\n\n";

$email_body .= "DOCTOR INFORMATION\n";
$email_body .= "───────────────────────────────────────────────────────────\n";
$email_body .= "Doctor Name:        Dr. $doctor_name\n";
$email_body .= "Practice Type:      $practice_type_display\n";
$email_body .= "License Number:     $license_number\n";
$email_body .= "NPI Number:         " . ($npi_number ?: 'Not provided') . "\n\n";

$email_body .= "PRACTICE INFORMATION\n";
$email_body .= "───────────────────────────────────────────────────────────\n";
$email_body .= "Company Name:       $company_name\n";
$email_body .= "Primary Contact:    " . ($primary_contact ?: 'Not provided') . "\n\n";

$email_body .= "CONTACT INFORMATION\n";
$email_body .= "───────────────────────────────────────────────────────────\n";
$email_body .= "Email:              $email\n";
$email_body .= "Phone:              $phone\n";
$email_body .= "Mobile Phone:       $mobile_phone\n\n";

$email_body .= "ADDRESS\n";
$email_body .= "───────────────────────────────────────────────────────────\n";
$email_body .= "Street Address:     $street_address_1\n";
if (!empty($street_address_2)) {
    $email_body .= "Suite/Unit:         $street_address_2\n";
}
$email_body .= "City:               $city\n";
$email_body .= "State/Region:       $state\n";
$email_body .= "Postal Code:        $postal_code\n";
$email_body .= "County:             $county\n\n";

$email_body .= "MARKETING & CONSENT\n";
$email_body .= "───────────────────────────────────────────────────────────\n";
$email_body .= "Referral Source:    $referral_source_display\n";
$email_body .= "Privacy Policy:     $privacy_policy\n";
$email_body .= "Marketing Consent:  $marketing_consent\n\n";

$email_body .= "═══════════════════════════════════════════════════════════\n";
$email_body .= "Submitted on: " . date('F j, Y \a\t g:i A T') . "\n";
$email_body .= "═══════════════════════════════════════════════════════════\n";

// Email headers
$headers = "From: $email\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

// Send email
if (mail($to_email, $email_subject, $email_body, $headers)) {
    echo json_encode(['success' => true, 'message' => 'Thank you for registering! Our team will contact you within 24 hours to complete your account setup.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Failed to send registration. Please call us at (833) 400-5443.']);
}
?>
