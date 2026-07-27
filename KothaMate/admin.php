<?php
require_once __DIR__ . '/includes/functions.php';

$user = currentUser();
if (!$user || ($user['role'] ?? 'user') !== 'admin') {
    redirect('');
}

$db = getDb();
$flash = getFlash();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && verifyCsrf()) {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_property') {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $price = (float) ($_POST['price_per_night'] ?? 0);
        $image = trim($_POST['image_url'] ?? '');
        $status = trim($_POST['status'] ?? 'available');

        if ($title === '' || $description === '' || $location === '' || $price <= 0) {
            setFlash('error', 'Please fill all required fields.');
            redirect('admin.php');
        }

        $slug = strtolower(preg_replace('/[^a-z0-9]+/', '-', $title));
        $stmt = $db->prepare('
            INSERT INTO properties (title, slug, description, location, price_per_night, image_url, status, owner_id)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([$title, $slug, $description, $location, $price, $image, $status, $user['id']]);
        setFlash('success', 'Property added.');
        redirect('admin.php');
    }

    if ($action === 'update_booking') {
        $bookingId = (int) ($_POST['booking_id'] ?? 0);
        $status = trim($_POST['status'] ?? 'pending');

        if ($bookingId > 0) {
            $stmt = $db->prepare('UPDATE bookings SET status = ? WHERE id = ?');
            $stmt->execute([$status, $bookingId]);
        }

        setFlash('success', 'Booking status updated.');
        redirect('admin.php');
    }
}

$properties = $db->query('SELECT * FROM properties ORDER BY created_at DESC')->fetchAll();
$bookings = $db->query('
    SELECT b.*, p.title, u.name
    FROM bookings b
    JOIN properties p ON p.id = b.property_id
    JOIN users u ON u.id = b.user_id
    ORDER BY b.created_at DESC
')->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin | KothaMate</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,wght@0,600;0,700;1,600&family=Work+Sans:wght@400;500;600;700&family=Space+Mono:wght@400;700&display=swap" rel="stylesheet">
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
                <span class="section-label">Admin panel</span>
                <h2>Manage properties and bookings</h2>
            </div>
        </div>

        <div class="admin-grid">
            <div class="panel card">
                <h3>Create property</h3>
                <form method="post" class="stack-form">
                    <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                    <input type="hidden" name="action" value="create_property">
                    <input type="text" name="title" placeholder="Title" required>
                    <textarea name="description" placeholder="Description" required></textarea>
                    <input type="text" name="location" placeholder="Location" required>
                    <input type="number" step="0.01" name="price_per_night" placeholder="Price" required>
                    <input type="url" name="image_url" placeholder="Image URL">
                    <select name="status">
                        <option value="available">Available</option>
                        <option value="booked">Booked</option>
                        <option value="maintenance">Maintenance</option>
                    </select>
                    <button class="btn btn-primary" type="submit">Create</button>
                </form>
            </div>

            <div class="panel card">
                <h3>Bookings</h3>
                <?php foreach ($bookings as $booking): ?>
                    <div class="booking-row">
                        <div>
                            <strong><?= e($booking['title']) ?></strong><br>
                            <span><?= e($booking['name']) ?> • <?= e($booking['status']) ?></span>
                        </div>
                        <form method="post" class="inline-form">
                            <input type="hidden" name="csrf_token" value="<?= e(csrfToken()) ?>">
                            <input type="hidden" name="action" value="update_booking">
                            <input type="hidden" name="booking_id" value="<?= (int) $booking['id'] ?>">
                            <select name="status">
                                <option value="pending" <?= $booking['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="confirmed" <?= $booking['status'] === 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                <option value="cancelled" <?= $booking['status'] === 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                            </select>
                            <button class="btn btn-small" type="submit">Update</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="section">
            <h3>Current properties</h3>
            <div class="property-grid">
                <?php foreach ($properties as $property): ?>
                    <article class="card property-card">
                        <img src="<?= e($property['image_url']) ?>" alt="<?= e($property['title']) ?>">
                        <div class="card-body">
                            <h3><?= e($property['title']) ?></h3>
                            <p><?= e($property['location']) ?></p>
                            <p><strong>Status:</strong> <?= e($property['status']) ?></p>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </main>
    <footer class="site-footer">
        <div class="container">
            <span class="brand">KothaMate</span>
            <small>KothaMate &mdash; Dhaka stays, plainly addressed.</small>
        </div>
    </footer>

</body>
</html>
