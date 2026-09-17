<?php
require_once __DIR__ . '/../config/database.php'; require_once __DIR__ . '/../includes/auth.php'; requireRole('student');
$statement = $pdo->prepare('SELECT r.*, t.name type_name FROM requests r JOIN request_types t ON t.id = r.type_id WHERE r.id = ? AND r.student_id = ?'); $statement->execute([(int) ($_GET['id'] ?? 0), $_SESSION['user_id']]); $request = $statement->fetch();
if (!$request) { http_response_code(404); exit('Request not found.'); }
$pageTitle = 'View request'; $basePath = '../'; require __DIR__ . '/../includes/header.php';
?>
<h1><?= e($request['subject']) ?></h1><div class="detail"><p><b>Type:</b> <?= e($request['type_name']) ?></p><p><b>Status:</b> <?= e($request['status']) ?></p><p><?= nl2br(e($request['description'])) ?></p><?php if ($request['staff_notes']): ?><hr><p><b>Staff notes:</b><br><?= nl2br(e($request['staff_notes'])) ?></p><?php endif; ?></div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
