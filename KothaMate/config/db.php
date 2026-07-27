<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = 'localhost';
$dbName = 'kothamate';
$dbUser = 'root';
$dbPass = '';

$pdo = null;

$scriptPath = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '/index.php');
$scriptDir = dirname($scriptPath);

if (basename($scriptDir) === 'api') {
    $scriptDir = dirname($scriptDir);
}

$scriptDir = rtrim($scriptDir, '/');
define('BASE_URL', $scriptDir === '' || $scriptDir === '/' ? '' : $scriptDir);

function getDb(): PDO
{
    global $pdo, $host, $dbName, $dbUser, $dbPass;

    if ($pdo instanceof PDO) {
        return $pdo;
    }

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $dsn = "mysql:host=$host;charset=utf8mb4";

    try {
        $tmp = new PDO($dsn, $dbUser, $dbPass, $options);
        $tmp->exec("CREATE DATABASE IF NOT EXISTS `$dbName`");
        $pdo = new PDO("mysql:host=$host;dbname=$dbName;charset=utf8mb4", $dbUser, $dbPass, $options);
    } catch (PDOException $e) {
        throw new Exception('Database connection failed: ' . $e->getMessage());
    }

    initializeDatabase($pdo);
    return $pdo;
}

function initializeDatabase(PDO $pdo): void
{
    $schemaPath = __DIR__ . '/../database/schema.sql';
    if (!file_exists($schemaPath)) {
        return;
    }

    $schema = file_get_contents($schemaPath);
    $schema = str_replace("\xEF\xBB\xBF", '', $schema);

    $statements = array_filter(
        array_map('trim', preg_split('/;\s*/', $schema)),
        static function ($statement) {
            return $statement !== '';
        }
    );

    foreach ($statements as $statement) {
        $pdo->exec($statement);
    }

    $userCount = (int) $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    if ($userCount === 0) {
        $hash = password_hash('admin123', PASSWORD_BCRYPT);
        $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)')
            ->execute(['Administrator', 'admin@kothamate.local', $hash, 'admin']);
    }

    $propertyCount = (int) $pdo->query('SELECT COUNT(*) FROM properties')->fetchColumn();
    if ($propertyCount === 0) {
        $seed = [
            [
                'Riverfront Family House',
                'riverfront-family-house',
                'A spacious family home with a rooftop patio and quiet views of the river.',
                'Savar',
                95,
                3,
                2,
                4,
                'https://images.unsplash.com/photo-1512918728675-ed5a9ecdebfd?auto=format&fit=crop&w=1200&q=80',
                'available',
                1,
            ],
            [
                'Modern Loft in Banani',
                'modern-loft-banani',
                'Bright loft with luxury finishes, fast internet, and a premium city view.',
                'Banani',
                130,
                2,
                2,
                3,
                'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?auto=format&fit=crop&w=1200&q=80',
                'available',
                1,
            ],
            [
                'Garden Apartment',
                'garden-apartment-dhanmondi',
                'A cozy apartment surrounded by greenery, perfect for short and long stays.',
                'Dhanmondi',
                82,
                2,
                1,
                2,
                'https://images.unsplash.com/photo-1494526585095-c41746248156?auto=format&fit=crop&w=1200&q=80',
                'available',
                1,
            ],
        ];

        $stmt = $pdo->prepare('
            INSERT INTO properties (
                title, slug, description, location, price_per_night, bedrooms, bathrooms, guests, image_url, status, owner_id
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ');

        foreach ($seed as $item) {
            $stmt->execute($item);
        }
    }
}

getDb();
