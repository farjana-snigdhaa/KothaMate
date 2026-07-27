<?php
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$response = ['ok' => false, 'message' => 'Invalid request'];
$action = $_POST['action'] ?? '';

if ($action === 'login') {
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($email === '' || $password === '') {
        $response['message'] = 'Please provide email and password.';
        echo json_encode($response);
        exit;
    }

    $db = getDb();
    $stmt = $db->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $response['ok'] = true;
        $response['message'] = 'Logged in successfully.';
        $response['user'] = ['name' => $user['name'], 'role' => $user['role']];
        echo json_encode($response);
        exit;
    }

    $response['message'] = 'Invalid credentials.';
    echo json_encode($response);
    exit;
}

if ($action === 'register') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');

    if ($name === '' || $email === '' || strlen($password) < 6) {
        $response['message'] = 'Please provide a name, valid email, and a password of at least 6 characters.';
        echo json_encode($response);
        exit;
    }

    $db = getDb();
    $check = $db->prepare('SELECT id FROM users WHERE email = ?');
    $check->execute([$email]);

    if ($check->fetch()) {
        $response['message'] = 'This email is already registered.';
        echo json_encode($response);
        exit;
    }

    $hash = password_hash($password, PASSWORD_BCRYPT);
    $db->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)')->execute([$name, $email, $hash, 'user']);

    $_SESSION['user_id'] = $db->lastInsertId();
    $response['ok'] = true;
    $response['message'] = 'Account created successfully.';
    $response['user'] = ['name' => $name, 'role' => 'user'];
    echo json_encode($response);
    exit;
}

if ($action === 'logout') {
    session_destroy();
    session_start();
    $response['ok'] = true;
    $response['message'] = 'Logged out.';
    echo json_encode($response);
    exit;
}

echo json_encode($response);
