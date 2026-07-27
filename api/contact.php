<?php
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($name === '' || $email === '' || $message === '') {
    echo json_encode(['ok' => false, 'message' => 'Please complete all fields.']);
    exit;
}

$db = getDb();
$stmt = $db->prepare('INSERT INTO contacts (name, email, message) VALUES (?, ?, ?)');
$stmt->execute([$name, $email, $message]);

echo json_encode(['ok' => true, 'message' => 'Thanks for your message.']);
