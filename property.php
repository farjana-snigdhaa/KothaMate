<?php
require_once __DIR__ . '/includes/functions.php';
$id = (int) ($_GET['id'] ?? 0);
$db = getDb();
$stmt = $db->prepare('SELECT * FROM properties WHERE id = ?');
$stmt->execute([$id]);
$property = $stmt->fetch();

if (!$property) {
    redirect('');
}

$user = currentUser();
$flash = getFlash();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($property['title']) ?> | KothaMate</title>
    <link rel="stylesheet" href="<?= asset('css/style.css') ?>">
</head>
<body>
    <header class="site-header">
        <div class="container nav">
            <a class="brand" href="<?= url('') ?>">KothaMate</a>
            <nav class="nav-links">
                <a href="<?= url('') ?>">Home</a>
                <?php if ($user): ?>
                    <a href="<?= url('dashboard.php') ?>">Dashboard</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main class="container section">
        <?php foreach ($flash as $item): ?>
            <div class="alert alert-<?= e($item['type']) ?>">
                <?= e($item['message']) ?>
            </div>
        <?php endforeach; ?>

        <div class="detail-grid">
            <div class="card detail-card">
                <img src="<?= e($property['image_url']) ?>" alt="<?= e($property['title']) ?>">
                <div class="card-body">
                    <div class="meta-row">
                        <span><?= e($property['location']) ?></span>
                        <span><?= e($property['bedrooms']) ?> bed</span>
                    </div>
                    <h2><?= e($property['title']) ?></h2>
                    <p><?= e($property['description']) ?></p>
                    <div class="detail-meta">
                        <span><strong>Location:</strong> <?= e($property['location']) ?></span>
                        <span><strong>Bedrooms:</strong> <?= e($property['bedrooms']) ?></span>
                        <span><strong>Bathrooms:</strong> <?= e($property['bathrooms']) ?></span>
                        <span><strong>Guests:</strong> <?= e($property['guests']) ?></span>
                    </div>
                    <div class="price-row">
                        <strong><?= formatCurrency((float) $property['price_per_night']) ?>/night</strong>
                    </div>
                </div>
            </div>

            <div class="panel card">
                <h3>Book this stay</h3>
                <?php if ($user): ?>
                    <form id="booking-form" class="stack-form">
                        <input type="hidden" name="property_id" value="<?= (int) $property['id'] ?>">
                        <label>Check in</label>
                        <input type="date" name="check_in" required>
                        <label>Check out</label>
                        <input type="date" name="check_out" required>
                        <label>Guests</label>
                        <input type="number" name="guests" min="1" max="10" value="2" required>
                        <button class="btn btn-primary" type="submit">Request booking</button>
                    </form>
                <?php else: ?>
                    <p>Please <a href="<?= url('') ?>">login</a> to request a booking.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <div id="toast" class="toast"></div>

    <script>
        window.BASE_URL = '<?= BASE_URL ?>';
    </script>
    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
