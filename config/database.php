<?php

$host = 'sql201.infinityfree.com';
$username = 'if0_42941501';
$password = 'QUh1WPxz3P';
$database = 'if0_42941501_student_request_system';

try {
    $dsn = "mysql:host={$host};dbname={$database};charset=utf8mb4";

    $pdo = new PDO($dsn, $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

} catch (PDOException $e) {

    die(
        'Database connection failed.<br><br>' .
        'Error: ' . htmlspecialchars($e->getMessage())
    );
}
