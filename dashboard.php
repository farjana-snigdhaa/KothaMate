<?php
<?php
require_once __DIR__ . '/includes/functions.php';

$user = currentUser();
if (!$user) {
    redirect('');
}

$db = getDb();
$stmt = $db->prepare('
    SELECT b.*, p.title, p.location, p.image_url
    FROM bookings b
    JOIN properties p ON p.id = b.property_id
    WHERE b.user_id = ?
    ORDER BY b.created_at DESC
');
$stmt->execute([$user['id']]);
$bookings = $stmt->fetchAll();
$flash = getFlash();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard | KothaMate</title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body>
    <header class="site-header">
        <div class="container nav">
            <a class="brand" href="<?= url('') ?>">KothaMate</a>
            <nav class="nav-links">
                <a href="<?= url('') ?>">Home</a>
            </nav>
        </div>
    </header>

    <main class="container section">
        <?php foreach ($flash as $item): ?>
            <div class="alert alert-<?= e($item['type']) ?>">
                <?= e($item['message']) ?>
            </div>
        <?php endforeach; ?>

        <div class="section-heading">
            <div>
                <span class="section-label">Your bookings</span>
                <h2>Stay overview</h2>
            </div>
        </div>

        <?php if (!$bookings): ?>
            <div class="panel card empty-state">
                <h3>No bookings yet</h3>
                <p>Start by browsing available properties and request your first stay.</p>
            </div>
        <?php else: ?>
            <div class="property-grid">
                <?php foreach ($bookings as $booking): ?>
                    <article class="card property-card">
                        <img src="<?= e($booking['image_url']) ?>" alt="<?= e($booking['title']) ?>">
                        <div class="card-body">
                            <h3><?= e($booking['title']) ?></h3>
                            <p><?= e($booking['location']) ?></p>
                            <p><strong>Status:</strong> <?= e($booking['status']) ?></p>
                            <p><strong>Dates:</strong> <?= e($booking['check_in']) ?> → <?= e($booking['check_out']) ?></p>
                            <p><strong>Total:</strong> <?= formatCurrency((float) $booking['total_price']) ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
