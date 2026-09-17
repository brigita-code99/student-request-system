<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireLogin()
{
    if (!isset($_SESSION['user_id'])) {
        header("Location: ../login.php");
        exit;
    }
}

function requireRole($role)
{
    requireLogin();

    if ($_SESSION['role'] !== $role) {
        header("Location: ../index.php");
        exit;
    }
}

function redirectForRole(string $role): void
{
    $locations = [
        'admin' => 'admin/dashboard.php',
        'staff' => 'staff/dashboard.php',
        'student' => 'student/dashboard.php',
    ];
    header('Location: ' . ($locations[$role] ?? 'index.php'));
    exit;
}

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}