<?php
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$q = trim($_GET['q'] ?? '');
$location = trim($_GET['location'] ?? '');
$minPrice = (float) ($_GET['min_price'] ?? 0);
$maxPrice = (float) ($_GET['max_price'] ?? 0);

$db = getDb();
$sql = "SELECT * FROM properties WHERE status = 'available'";
$params = [];

if ($q !== '') {
    $sql .= ' AND (title LIKE ? OR description LIKE ? OR location LIKE ?)';
    $like = '%' . $q . '%';
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}

if ($location !== '') {
    $sql .= ' AND location LIKE ?';
    $params[] = '%' . $location . '%';
}

if ($minPrice > 0) {
    $sql .= ' AND price_per_night >= ?';
    $params[] = $minPrice;
}

if ($maxPrice > 0) {
    $sql .= ' AND price_per_night <= ?';
    $params[] = $maxPrice;
}

$sql .= ' ORDER BY created_at DESC';

$stmt = $db->prepare($sql);
$stmt->execute($params);

echo json_encode($stmt->fetchAll());
