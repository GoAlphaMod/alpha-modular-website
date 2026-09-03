<?php
declare(strict_types=1);

header('Content-Type: application/json; charset=UTF-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

if (!empty($_POST['website'] ?? '')) {
    echo json_encode(['success' => true]);
    exit;
}

function clean_line(string $value): string {
    return trim(str_replace(["\r", "\n"], ' ', strip_tags($value)));
}

function clean_message(string $value): string {
    return trim(strip_tags($value));
}

$name = clean_line((string)($_POST['name'] ?? ''));
$company = clean_line((string)($_POST['company'] ?? ''));
$email = filter_var(trim((string)($_POST['email'] ?? '')), FILTER_VALIDATE_EMAIL);
$phone = clean_line((string)($_POST['phone'] ?? ''));
$location = clean_line((string)($_POST['location'] ?? ''));
$intent = clean_line((string)($_POST['intent'] ?? ''));
$product = clean_line((string)($_POST['product'] ?? ''));
$details = clean_message((string)($_POST['message'] ?? ''));

if ($name === '' || $company === '' || $email === false || $phone === '') {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => 'Please complete all required fields.']);
    exit;
}

$to = 'Sales@GoAlphaModular.com';
$safeProduct = $product !== '' ? $product : 'General Inquiry';
$subject = 'Alpha Modular Quote Request: ' . $safeProduct . ' — ' . $company;
$body = implode("\n", [
    'NEW ALPHA MODULAR QUOTE REQUEST',
    '================================',
    '',
    'Name: ' . $name,
    'Company: ' . $company,
    'Email: ' . $email,
    'Phone: ' . $phone,
    'Project location: ' . ($location !== '' ? $location : 'Not provided'),
    'Rent or buy: ' . ($intent !== '' ? $intent : 'Not specified'),
    'Product: ' . $safeProduct,
    '',
    'PROJECT DETAILS',
    '---------------',
    $details !== '' ? $details : 'No additional details provided.',
    '',
    'Submitted: ' . date('F j, Y \a\t g:i A T'),
]);

$headers = [
    'From: Alpha Modular Website <Sales@GoAlphaModular.com>',
    'Reply-To: ' . $name . ' <' . $email . '>',
    'Content-Type: text/plain; charset=UTF-8',
    'X-Mailer: PHP/' . phpversion(),
];

$sent = mail($to, $subject, $body, implode("\r\n", $headers));

if (!$sent) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'The message could not be sent.']);
    exit;
}

echo json_encode(['success' => true]);
