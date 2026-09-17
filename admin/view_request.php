<?php
require_once __DIR__ . '/../config/database.php'; require_once __DIR__ . '/../includes/auth.php'; requireRole('admin');
$statement=$pdo->prepare('SELECT r.*,u.name student_name,u.email,t.name type_name FROM requests r JOIN users u ON u.id=r.student_id JOIN request_types t ON t.id=r.type_id WHERE r.id=?');$statement->execute([(int)($_GET['id']??0)]);$request=$statement->fetch();if(!$request){http_response_code(404);exit('Request not found.');}
$pageTitle='Request details';$basePath='../';require __DIR__.'/../includes/header.php';
?>
<h1><?=e($request['subject'])?></h1><div class="detail"><p><b>Student:</b> <?=e($request['student_name'])?> (<?=e($request['email'])?>)</p><p><b>Type:</b> <?=e($request['type_name'])?></p><p><b>Status:</b> <?=e($request['status'])?></p><p><?=nl2br(e($request['description']))?></p><?php if($request['staff_notes']):?><hr><p><b>Staff notes:</b><br><?=nl2br(e($request['staff_notes']))?></p><?php endif;?></div>
<?php require __DIR__.'/../includes/footer.php'; ?>
