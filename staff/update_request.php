<?php
require_once __DIR__ . '/../config/database.php'; require_once __DIR__ . '/../includes/auth.php'; requireRole('staff');
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { header('Location: requests.php'); exit; }
$statement=$pdo->prepare('UPDATE requests SET status=?, staff_notes=? WHERE id=?'); $statement->execute([$_POST['status'], trim($_POST['staff_notes']??''), (int)$_POST['id']]); header('Location: view_request.php?id='.(int)$_POST['id']); exit;
