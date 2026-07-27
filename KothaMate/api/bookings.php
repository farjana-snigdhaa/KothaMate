<?php
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['ok' => false, 'message' => 'Please log in first.']);
    exit;
}

$propertyId = (int) ($_POST['property_id'] ?? 0);
$checkIn = trim($_POST['check_in'] ?? '');
$checkOut = trim($_POST['check_out'] ?? '');
$guests = (int) ($_POST['guests'] ?? 1);

if ($propertyId <= 0 || $checkIn === '' || $checkOut === '' || $guests <= 0) {
    echo json_encode(['ok' => false, 'message' => 'Please complete all booking fields.']);
    exit;
}

$db = getDb();
$stmt = $db->prepare('SELECT * FROM properties WHERE id = ?');
$stmt->execute([$propertyId]);
$property = $stmt->fetch();

if (!$property) {
    echo json_encode(['ok' => false, 'message' => 'Property not found.']);
    exit;
}

$from = new DateTime($checkIn);
$to = new DateTime($checkOut);

if ($to <= $from) {
    echo json_encode(['ok' => false, 'message' => 'Check-out must be after check-in.']);
    exit;
}

$nights = max(1, (int) $from->diff($to)->days);
$total = (float) $property['price_per_night'] * $nights;

$insert = $db->prepare('
    INSERT INTO bookings (user_id, property_id, check_in, check_out, guests, total_price, status)
    VALUES (?, ?, ?, ?, ?, ?, ?)
');
$insert->execute([$_SESSION['user_id'], $propertyId, $checkIn, $checkOut, $guests, $total, 'pending']);

echo json_encode(['ok' => true, 'message' => 'Booking request submitted successfully.']);
