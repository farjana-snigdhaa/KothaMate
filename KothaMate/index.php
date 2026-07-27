<?php
require_once __DIR__ . '/includes/functions.php';
$db = getDb();
$featured = $db->query("SELECT * FROM properties WHERE status = 'available' ORDER BY created_at DESC LIMIT 6")->fetchAll();
$user = currentUser();
$flash = getFlash();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>KothaMate</title>
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
                <a href="#properties">Properties</a>
                <a href="#contact">Contact</a>
                <?php if ($user): ?>
                    <a href="<?= url('dashboard.php') ?>">Dashboard</a>
                    <?php if (($user['role'] ?? 'user') === 'admin'): ?>
                        <a href="<?= url('admin.php') ?>">Admin</a>
                    <?php endif; ?>
                    <a href="#" id="logout-link">Logout</a>
                <?php else: ?>
                    <a href="#" class="nav-link" data-target="login-form">Login</a>
                    <a href="#" class="nav-link" data-target="register-form">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <main>
        <?php foreach ($flash as $item): ?>
            <div class="container">
                <div class="alert alert-<?= e($item['type']) ?>">
                    <?= e($item['message']) ?>
                </div>
            </div>
        <?php endforeach; ?>

        <section class="hero">
            <div class="container hero-grid">
                <div class="hero-copy">
                    <span class="hero-badge">Premium homes & stays</span>
                    <h1>Stay in homes that feel like your own.</h1>
                    <p>Explore curated apartments, family homes, and premium retreats with a smooth booking experience.</p>
                    <div class="hero-actions">
                        <a class="btn btn-primary" href="#properties">Browse stays</a>
                        <a class="btn btn-secondary" href="#contact">Contact us</a>
                    </div>
                </div>
                <div class="hero-card">
                    <h3>Why guests choose KothaMate</h3>
                    <ul class="feature-list">
                        <li>Beautiful, handpicked properties</li>
                        <li>Fast, secure booking requests</li>
                        <li>Easy contact and support</li>
                        <li>Admin-managed property listings</li>
                    </ul>
                </div>
            </div>
        </section>

        <section class="container section">
            <div class="split-grid">
                <div class="panel card">
                    <h2>Discover a better stay</h2>
                    <p>From cozy apartments to luxurious homes, KothaMate helps guests book with confidence and style.</p>
                </div>

                <div class="panel card">
                    <?php if (!$user): ?>
                        <h2>Get started</h2>
                        <div class="auth-grid">
                            <form id="login-form" class="stack-form">
                                <h3>Login</h3>
                                <input type="email" name="email" placeholder="Email" required>
                                <input type="password" name="password" placeholder="Password" required>
                                <button class="btn btn-primary" type="submit">Login</button>
                            </form>

                            <form id="register-form" class="stack-form">
                                <h3>Register</h3>
                                <input type="text" name="name" placeholder="Your name" required>
                                <input type="email" name="email" placeholder="Email" required>
                                <input type="password" name="password" placeholder="Password" required>
                                <button class="btn btn-secondary" type="submit">Create account</button>
                            </form>
                        </div>
                    <?php else: ?>
                        <h2>Welcome back</h2>
                        <p>Hello, <?= e($user['name']) ?>. Your dashboard is ready for the next stay.</p>
                        <a class="btn btn-primary" href="<?= url('dashboard.php') ?>">Go to dashboard</a>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <section class="container section" id="properties">
            <div class="section-heading">
                <div>
                    <span class="section-label">Featured stays</span>
                    <h2>Browse premium properties</h2>
                </div>
                <form id="property-search" class="search-form">
                    <input type="text" name="q" placeholder="Keyword">
                    <input type="text" name="location" placeholder="Location">
                    <input type="number" name="min_price" placeholder="Min price">
                    <input type="number" name="max_price" placeholder="Max price">
                    <button class="btn btn-primary" type="submit">Search</button>
                </form>
            </div>

            <div id="property-list" class="property-grid">
                <?php foreach ($featured as $property): ?>
                    <article class="card property-card">
                        <img src="<?= e($property['image_url']) ?>" alt="<?= e($property['title']) ?>">
                        <div class="card-body">
                            <div class="meta-row">
                                <span><?= e($property['location']) ?></span>
                                <span><?= e($property['bedrooms']) ?> bed</span>
                            </div>
                            <h3><?= e($property['title']) ?></h3>
                            <p><?= e($property['description']) ?></p>
                            <div class="card-footer">
                                <strong><?= formatCurrency((float) $property['price_per_night']) ?>/night</strong>
                                <a class="btn btn-small" href="<?= url('property.php?id=' . (int) $property['id']) ?>">View</a>
                            </div>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="container section" id="contact">
            <div class="panel card">
                <div class="section-heading">
                    <div>
                        <span class="section-label">Contact us</span>
                        <h2>Need help choosing the right stay?</h2>
                    </div>
                </div>
                <form id="contact-form" class="stack-form">
                    <input type="text" name="name" placeholder="Your name" required>
                    <input type="email" name="email" placeholder="Email" required>
                    <textarea name="message" placeholder="Write your message..." required></textarea>
                    <button class="btn btn-primary" type="submit">Send message</button>
                </form>
            </div>
        </section>
    </main>

    <footer class="site-footer">
        <div class="container">
            <span class="brand">KothaMate</span>
            <small>KothaMate &mdash; Dhaka stays, plainly addressed.</small>
        </div>
    </footer>

    <div id="toast" class="toast"></div>

    <script>
        window.BASE_URL = '<?= BASE_URL ?>';
    </script>
    <script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
