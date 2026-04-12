<?php
/**
 * Contact form handler — sends plain-text email to company inbox.
 * Expects POST (multipart/form-data). Returns JSON.
 *
 * Hosting note: PHP mail() must be allowed by your server. If emails do not arrive,
 * configure SMTP (e.g. PHPMailer) or use a transactional provider.
 */
declare(strict_types=1);

session_start();

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('X-Robots-Tag: noindex, nofollow', true);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'error' => 'Method not allowed']);
    exit;
}

function str_utf8_limit(string $s, int $max): string
{
    $s = strip_tags($s);
    if (function_exists('mb_substr')) {
        return mb_substr(trim($s), 0, $max, 'UTF-8');
    }
    return substr(trim($s), 0, $max);
}

/** Prevent header injection / log noise */
function strip_crlf(string $s): string
{
    return str_replace(["\r", "\n", "\0"], '', $s);
}

$companyTo = 'info@strinvestment.com.ng';

$data = $_POST;

// Honeypot — filled = bot; pretend success
if (!empty($data['website'] ?? '')) {
    echo json_encode(['ok' => true, 'message' => 'Thank you.']);
    exit;
}

$token = (string)($data['csrf_token'] ?? '');
$sess = (string)($_SESSION['csrf_contact'] ?? '');
if ($sess === '' || $token === '' || !hash_equals($sess, $token)) {
    http_response_code(403);
    echo json_encode(['ok' => false, 'error' => 'Session expired. Please refresh the page and try again.']);
    exit;
}

$now = time();

// Minimum time on page (anti-bot)
$started = (int)($data['form_started_at'] ?? 0);
if ($started > 0 && ($now - $started) < 3) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Please complete the form before sending.']);
    exit;
}

// Rate limit: one successful send per 60 seconds per session
if (!empty($_SESSION['contact_last_ok']) && ($now - (int)$_SESSION['contact_last_ok']) < 60) {
    http_response_code(429);
    echo json_encode(['ok' => false, 'error' => 'Please wait a minute before sending another message.']);
    exit;
}

$name = str_utf8_limit($data['name'] ?? '', 80);
$phone = str_utf8_limit($data['phone'] ?? '', 24);
$phoneStripped = preg_replace('/[^\d+\-\s()]/', '', $phone);
$phone = str_utf8_limit($phoneStripped !== null ? $phoneStripped : '', 24);

$emailRaw = str_utf8_limit($data['email'] ?? '', 120);
$emailRaw = strip_crlf($emailRaw);

if ($name === '' || $phone === '' || $emailRaw === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Please complete name, phone, and email.']);
    exit;
}

if (!filter_var($emailRaw, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Please enter a valid email address.']);
    exit;
}

$product = $data['product'] ?? 'general';
$allowedProducts = ['general', 'personal', 'advance', 'school', 'sme'];
if (!in_array($product, $allowedProducts, true)) {
    $product = 'general';
}

$productLabels = [
    'general' => 'Loan enquiry',
    'personal' => 'Personal loan',
    'advance' => 'Salary advance',
    'school' => 'Back to school',
    'sme' => 'SME term loan',
];
$productLabel = $productLabels[$product];

$message = str_utf8_limit($data['message'] ?? '', 1200);
$message = str_replace(["\r\n", "\r"], "\n", $message);
$messageCollapsed = preg_replace("/\n{4,}/", "\n\n\n", $message);
$message = $messageCollapsed !== null ? $messageCollapsed : $message;

if (trim($message) === '') {
    http_response_code(400);
    echo json_encode(['ok' => false, 'error' => 'Please enter a message.']);
    exit;
}

$subject = 'Enquiry — STR Investment — ' . $productLabel;
$subjectHeader = '=?UTF-8?B?' . base64_encode($subject) . '?=';

$body = "New enquiry from strinvestment.com.ng contact form\n";
$body .= "Submitted: " . gmdate('Y-m-d H:i:s') . " UTC\n\n";
$body .= "Name: {$name}\n";
$body .= "Phone: {$phone}\n";
$body .= "Email: {$emailRaw}\n";
$body .= "Product: {$productLabel}\n\n";
$body .= "Message:\n{$message}\n";

// From must often match a domain mailbox on shared hosting; company address is reliable for deliverability tests.
$fromSafe = strip_crlf($companyTo);
$replyTo = $emailRaw;

$headers = [
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'Content-Transfer-Encoding: 8bit',
    'From: STR Investment Website <' . $fromSafe . '>',
    'Reply-To: ' . $replyTo,
    'X-Mailer: STR-Contact/1',
];

$extraParams = '-f' . $fromSafe;

$sent = @mail($companyTo, $subjectHeader, $body, implode("\r\n", $headers), $extraParams);

if (!$sent) {
    http_response_code(500);
    echo json_encode([
        'ok' => false,
        'error' => 'We could not send your message from the server. Please email us directly or call 09054984777.',
    ]);
    exit;
}

// Acknowledgment to the visitor (non-fatal if this fails — company copy already sent)
$nameParts = preg_split('/\s+/u', trim($name), 2, PREG_SPLIT_NO_EMPTY);
$firstName = isset($nameParts[0]) ? str_utf8_limit($nameParts[0], 40) : '';
$greeting = $firstName !== '' ? 'Hello ' . $firstName . ',' : 'Hello,';

$ackSubject = 'We received your message — STR Investment Services Limited';
$ackSubjectHeader = '=?UTF-8?B?' . base64_encode($ackSubject) . '?=';

$ackBody = $greeting . "\n\n";
$ackBody .= "Thank you for contacting STR Investment Services Limited.\n\n";
$ackBody .= 'We have received your enquiry regarding "' . $productLabel . "\" and will respond as soon as we can.\n\n";
$ackBody .= "If your matter is urgent, call or WhatsApp: 09054984777\n";
$ackBody .= 'General email: ' . $companyTo . "\n\n";
$ackBody .= "Kind regards,\nSTR Investment Services Limited\n";

$ackHeaders = [
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'Content-Transfer-Encoding: 8bit',
    'From: STR Investment Services Limited <' . $fromSafe . '>',
    'Reply-To: ' . $fromSafe,
    'X-Mailer: STR-Contact/1',
];

@mail($emailRaw, $ackSubjectHeader, $ackBody, implode("\r\n", $ackHeaders), $extraParams);

$_SESSION['contact_last_ok'] = $now;
$_SESSION['csrf_contact'] = bin2hex(random_bytes(32));

echo json_encode([
    'ok' => true,
    'message' => 'Thank you. We have received your message and will respond soon. Check your inbox for a short confirmation email.',
    'csrf' => $_SESSION['csrf_contact'],
]);
