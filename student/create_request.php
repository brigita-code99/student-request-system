<?php
require_once __DIR__ . '/../config/database.php'; require_once __DIR__ . '/../includes/auth.php'; requireRole('student');
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $typeId = (int) ($_POST['type_id'] ?? 0);
    $subject = trim($_POST['subject'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $typeStatement = $pdo->prepare('SELECT id FROM request_types WHERE id = ?');
    $typeStatement->execute([$typeId]);

    if ($subject === '' || $description === '' || !$typeStatement->fetchColumn()) {
        header('Location: create_request.php?error=' . urlencode('Please complete all request fields.'));
        exit;
    }

    $statement = $pdo->prepare(
        'INSERT INTO requests (student_id, type_id, subject, description)
         VALUES (:student_id, :type_id, :subject, :description)'
    );
    $statement->execute([
        ':student_id' => (int) $_SESSION['user_id'],
        ':type_id' => $typeId,
        ':subject' => $subject,
        ':description' => $description,
    ]);
    header('Location: my_requests.php?created=1'); exit;
}
$types = $pdo->query('SELECT * FROM request_types ORDER BY name')->fetchAll();
$pageTitle = 'Create request'; $basePath = '../'; require __DIR__ . '/../includes/header.php';
?>
<h1>Create a request</h1>
<?php if (isset($_GET['error'])): ?><div class="alert error"><?= e($_GET['error']) ?></div><?php endif; ?>
<form class="form-card" method="post">
    <label>Request type<select name="type_id" required><?php foreach ($types as $type): ?><option value="<?= (int) $type['id'] ?>"><?= e($type['name']) ?></option><?php endforeach; ?></select></label>
    <label>Subject<input name="subject" maxlength="180" required></label>
    <label>Description<textarea name="description" rows="7" required></textarea></label>
    <button class="button" type="submit">Submit request</button>
</form>
<?php require __DIR__ . '/../includes/footer.php'; ?>
