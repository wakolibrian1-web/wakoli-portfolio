<?php
/**
 * wenyaa.php
 * Handles contact form submission, validates, saves to DB,
 * then redirects with ?status= BEFORE #contact so JavaScript
 * can read it via URLSearchParams.
 */

require_once 'db_connect.php';

// ── Only accept POST ──────────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// ── Collect & sanitise ────────────────────────────────────────────────────────
$name    = trim($_POST['name']    ?? '');
$email   = trim($_POST['email']   ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

// ── Validate ──────────────────────────────────────────────────────────────────
$errors = [];

if (empty($name) || mb_strlen($name) > 254) {
    $errors[] = 'Please enter a valid name (max 254 characters).';
}
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 254) {
    $errors[] = 'Please enter a valid email address.';
}

$allowed_subjects = ['Technical inquiry','Technical Inquiry','General Inquiry','Business Partnership','Feedback','Other'];
if (!in_array($subject, $allowed_subjects, true)) {
    $errors[] = 'Please select a valid subject.';
}
if (empty($message) || mb_strlen($message) > 5000) {
    $errors[] = 'Please enter a message (max 5,000 characters).';
}

// ── Redirect with validation errors ──────────────────────────────────────────
if (!empty($errors)) {
    $query = http_build_query(['status' => 'error', 'message' => implode(' ', $errors)]);
    // ✅ Query string BEFORE hash so URLSearchParams can read it
    header('Location: index.php?' . $query . '#contact');
    exit;
}

// ── Save to database ──────────────────────────────────────────────────────────
try {
    $conn = get_db_connection();
} catch (RuntimeException $e) {
    header('Location: index.php?status=error&message=Server+error.+Please+try+again.#contact');
    exit;
}

$stmt = $conn->prepare(
    'INSERT INTO contact_messages (name, email, subject, message, submitted_at)
     VALUES (?, ?, ?, ?, NOW())'
);

if (!$stmt) {
    error_log('Prepare failed: ' . $conn->error);
    $conn->close();
    header('Location: index.php?status=error&message=Server+error.+Please+try+again.#contact');
    exit;
}

// Sanitise before storing
$name    = htmlspecialchars($name,    ENT_QUOTES, 'UTF-8');
$email   = htmlspecialchars($email,   ENT_QUOTES, 'UTF-8');
$subject = htmlspecialchars($subject, ENT_QUOTES, 'UTF-8');
$message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

$stmt->bind_param('ssss', $name, $email, $subject, $message);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    // ✅ Query string BEFORE hash
    header('Location: index.php?status=success#contact');
    exit;
} else {
    error_log('Execute failed: ' . $stmt->error);
    $stmt->close();
    $conn->close();
    header('Location: index.php?status=error&message=Could+not+save+your+message.+Please+try+again.#contact');
    exit;
}
