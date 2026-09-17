<?php
require_once __DIR__ . '/auth.php';
$pageTitle = $pageTitle ?? 'Student Request System';
$basePath = $basePath ?? '';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($pageTitle) ?> | Student Request System</title>
    <link rel="stylesheet" href="<?= $basePath ?>assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
</head>
<body>
<header class="site-header">
    <a class="brand" href="<?= $basePath ?>index.php">Student Request System</a>
    <?php if (isset($_SESSION['user_id'])): ?>
        <nav>
            <span><?= e($_SESSION['name']) ?> (<?= e($_SESSION['role']) ?>)</span>
            <a href="<?= $basePath ?>logout.php">Log out</a>
        </nav>
    <?php endif; ?>
</header>
