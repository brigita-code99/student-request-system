<?php
declare(strict_types=1);

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

// Get form values
$email = trim($_POST['email'] ?? '');
$plainPassword = $_POST['password'] ?? '';

// Validate input
if ($email === '' || $plainPassword === '') {
    header('Location: login.php?error=' . urlencode('Please enter your email and password.'));
    exit;
}

try {
    // Find user by email
    $statement = $pdo->prepare("
        SELECT id, name, email, password, role, status
        FROM users
        WHERE email = ?
        LIMIT 1
    ");

    $statement->execute([$email]);
    $user = $statement->fetch(PDO::FETCH_ASSOC);

    // User does not exist
    if (!$user) {
        header('Location: login.php?error=' . urlencode('Invalid email or password.'));
        exit;
    }

    // Check account status if your table has a status column
    if (isset($user['status']) && $user['status'] !== 'active') {
        header('Location: login.php?error=' . urlencode('Your account is not active.'));
        exit;
    }

    // Check password
    if (!password_verify($plainPassword, $user['password'])) {
        header('Location: login.php?error=' . urlencode('Invalid email or password.'));
        exit;
    }

    // Regenerate session ID after successful login
    session_regenerate_id(true);

    // Store login information
    $_SESSION['user_id'] = (int) $user['id'];
    $_SESSION['name'] = $user['name'] ?? '';
    $_SESSION['email'] = $user['email'];
    $_SESSION['role'] = strtolower(trim($user['role']));

    // Redirect based on role
    redirectForRole($_SESSION['role']);

    exit;

} catch (PDOException $e) {

    // Log the actual database error
    error_log('Login database error: ' . $e->getMessage());

    // Send the user back to login
    header('Location: login.php?error=' . urlencode(
        'A database error occurred. Please try again.'
    ));
    exit;

} catch (Throwable $e) {

    // Log unexpected PHP errors
    error_log('Login error: ' . $e->getMessage());

    header('Location: login.php?error=' . urlencode(
        'An unexpected error occurred. Please try again.'
    ));
    exit;
}
