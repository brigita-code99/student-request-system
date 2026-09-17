<?php
require_once __DIR__ . '/includes/auth.php';
if (isset($_SESSION['role'])) {
    redirectForRole($_SESSION['role']);
}
header('Location: login.php');
exit;