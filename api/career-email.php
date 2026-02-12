<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit();
}

// Turnstile secret key
$secret = "0x4AAAAAACat4HvcF4_htSbJz4ETthfPl4I";

// Get the Turnstile token
$token = $_POST['cf-turnstile-response'] ?? '';

if (empty($token)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Turnstile token is missing']);
    exit();
}

// Verify Turnstile token
$verifyResponse = file_get_contents(
    "https://challenges.cloudflare.com/turnstile/v0/siteverify?secret=$secret&response=$token"
);

$result = json_decode($verifyResponse, true);

if (!$result["success"]) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Turnstile verification failed']);
    exit();
}

// Get form data
$name = $_POST['name'] ?? '';
$email = $_POST['email'] ?? '';
$position = $_POST['position'] ?? '';
$coverLetter = $_POST['cover_letter'] ?? '';

// Validate required fields
if (empty($name) || empty($email)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Name and email are required']);
    exit();
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email format']);
    exit();
}

// Handle file upload
$resumePath = '';
$resumeName = '';
if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
    $resumeName = $_FILES['resume']['name'];
    $resumeTmp = $_FILES['resume']['tmp_name'];
    $resumeSize = $_FILES['resume']['size'];
    
    // Validate file size (max 25MB)
    if ($resumeSize > 25 * 1024 * 1024) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Resume file size exceeds 25MB']);
        exit();
    }
    
    // Validate file type
    $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $resumeTmp);
    finfo_close($finfo);
    
    if (!in_array($mimeType, $allowedTypes)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only PDF and DOC files are allowed']);
        exit();
    }
}

// Prepare email
$to = "chuzhishen@gmail.com"; // Change this to your HR email
$subject = "New Job Application - " . ($position ?: 'General Application');

// Create email body
$emailBody = "New job application received:\n\n";
$emailBody .= "Name: $name\n";
$emailBody .= "Email: $email\n";
$emailBody .= "Position: " . ($position ?: 'Not specified') . "\n\n";
$emailBody .= "Cover Letter:\n$coverLetter\n\n";
$emailBody .= "---\n";
$emailBody .= "Submitted: " . date('Y-m-d H:i:s') . "\n";

// Email headers
$headers = "From: noreply@qci.com.my\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "X-Mailer: PHP/" . phpversion();

// If resume was uploaded, attach it
if (!empty($resumeName)) {
    $boundary = md5(time());
    $headers .= "\r\nMIME-Version: 1.0\r\n";
    $headers .= "Content-Type: multipart/mixed; boundary=\"$boundary\"\r\n";
    
    $message = "--$boundary\r\n";
    $message .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $message .= "Content-Transfer-Encoding: 7bit\r\n\r\n";
    $message .= $emailBody . "\r\n\r\n";
    
    // Attach resume
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
        $fileContent = chunk_split(base64_encode(file_get_contents($resumeTmp)));
        $message .= "--$boundary\r\n";
        $message .= "Content-Type: $mimeType; name=\"$resumeName\"\r\n";
        $message .= "Content-Transfer-Encoding: base64\r\n";
        $message .= "Content-Disposition: attachment; filename=\"$resumeName\"\r\n\r\n";
        $message .= $fileContent . "\r\n\r\n";
    }
    
    $message .= "--$boundary--";
} else {
    $message = $emailBody;
}

// Send email
$mailSent = mail($to, $subject, $message, $headers);

if ($mailSent) {
    echo json_encode([
        'success' => true,
        'message' => 'Application submitted successfully! We will contact you soon.'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Failed to send email. Please try again later.'
    ]);
}