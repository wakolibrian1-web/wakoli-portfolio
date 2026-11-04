<?php
/**
 * wenyaa.php
 * Handles the contact form submission.
 * Validates input, saves to MySQL via XAMPP, and redirects back with a status.
 */

require_once 'db_connect.php';

// ── Only accept POST requests ─────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: INDEX.HTML');
    exit;
}

// ── Collect & sanitise inputs ─────────────────────────────────────────────────
$name    = trim(htmlspecialchars($_POST['name']    ?? '', ENT_QUOTES, 'UTF-8'));
$email   = trim(htmlspecialchars($_POST['email']   ?? '', ENT_QUOTES, 'UTF-8'));
$subject = trim(htmlspecialchars($_POST['subject'] ?? '', ENT_QUOTES, 'UTF-8'));
$message = trim(htmlspecialchars($_POST['message'] ?? '', ENT_QUOTES, 'UTF-8'));

// ── Server-side validation ────────────────────────────────────────────────────
$errors = [];

if (empty($name) || strlen($name) > 254) {
    $errors[] = 'Please enter a valid name (max 254 characters).';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
    $errors[] = 'Please enter a valid email address.';
}

$allowed_subjects = [
    'Technical inquiry',
    'General Inquiry',
    'Business Partnership',
    'Feedback',
    'Other'
];
if (!in_array($subject, $allowed_subjects, true)) {
    $errors[] = 'Please select a valid subject.';
}

if (empty($message) || strlen($message) > 5000) {
    $errors[] = 'Please enter a message (max 5,000 characters).';
}

// Redirect back with errors if validation fails
if (!empty($errors)) {
    $query = http_build_query([
        'status'  => 'error',
        'message' => implode(' ', $errors)
    ]);
    header('Location: INDEX.HTML#contact?' . $query);
    exit;
}

// ── Save to database ──────────────────────────────────────────────────────────
$conn = get_db_connection();

$sql  = 'INSERT INTO contact_messages (name, email, subject, message, submitted_at)
         VALUES (?, ?, ?, ?, NOW())';

$stmt = $conn->prepare($sql);

if (!$stmt) {
    error_log('Prepare failed: ' . $conn->error);
    header('Location: INDEX.HTML#contact?status=error&message=Server+error.+Please+try+again.');
    exit;
}

$stmt->bind_param('ssss', $name, $email, $subject, $message);

if ($stmt->execute()) {
    $stmt->close();
    $conn->close();
    // Success — redirect back to the contact section with a success flag
    header('Location: INDEX.HTML#contact?status=success');
    exit;
} else {
    error_log('Execute failed: ' . $stmt->error);
    $stmt->close();
    $conn->close();
    header('Location: INDEX.HTML#contact?status=error&message=Could+not+save+your+message.+Please+try+again.');
    exit;
}
