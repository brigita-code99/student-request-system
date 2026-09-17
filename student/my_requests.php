<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';
requireRole('student');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $typeId = (int) ($_POST['type_id'] ?? 0);
    $subject = trim((string) ($_POST['subject'] ?? ''));
    $description = trim((string) ($_POST['description'] ?? ''));

    $typeStatement = $pdo->prepare('SELECT id FROM request_types WHERE id = ?');
    $typeStatement->execute([$typeId]);

    if ($subject === '' || $description === '' || !$typeStatement->fetchColumn()) {
        header('Location: my_requests.php?error=' . urlencode('Please complete all request fields.'));
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

    header('Location: my_requests.php?created=1');
    exit;
}

$statement = $pdo->prepare(
    'SELECT r.*, t.name type_name
     FROM requests r
     JOIN request_types t ON t.id = r.type_id
     WHERE student_id = ?
     ORDER BY r.created_at DESC'
);
$statement->execute([$_SESSION['user_id']]);
$requests = $statement->fetchAll();

$types = $pdo->query('SELECT * FROM request_types ORDER BY name')->fetchAll();

$pageTitle = 'My requests';
$basePath = '../';
require __DIR__ . '/../includes/header.php';
?>

<div class="requests-page student-requests-page">
    <div class="requests-heading">
        <div>
            <h1>My requests</h1>
            <p class="muted">Track the status of your submitted requests.</p>
        </div>

        <div class="requests-heading-actions">
            <button
                type="button"
                class="button add-button button-compact open-create-request"
            >
                + New request
            </button>
            <a href="dashboard.php" class="button secondary button-compact back-dashboard-button">Back to Dashboard</a>
        </div>
    </div>

    <?php if (isset($_GET['created'])): ?>
        <div class="alert success">Your request was submitted.</div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert error"><?= e($_GET['error']) ?></div>
    <?php endif; ?>

    <div class="requests-table-wrap student-request-table-wrap">
        <table class="requests-table student-request-table">
            <thead>
                <tr>
                    <th>Request #</th>
                    <th>Subject</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $request): ?>
                    <?php
                    $status = trim((string) $request['status']);
                    $statusClass = match (strtolower($status)) {
                        'pending' => 'pending',
                        'in progress' => 'in-progress',
                        'resolved' => 'resolved',
                        'rejected' => 'rejected',
                        default => 'default',
                    };
                    ?>
                    <tr>
                        <td class="student-request-id">#<?= (int) $request['id'] ?></td>
                        <td>
                            <button
                                type="button"
                                class="subject-link"
                                data-subject="<?= e($request['subject']) ?>"
                                data-student=""
                                data-email=""
                                data-type="<?= e($request['type_name']) ?>"
                                data-status="<?= e($request['status']) ?>"
                                data-created="<?= e($request['created_at']) ?>"
                                data-description="<?= e($request['description'] ?? '') ?>"
                                data-staff-notes="<?= e($request['staff_notes'] ?? '') ?>"
                                title="View request details"
                            >
                                <?= e($request['subject']) ?>
                            </button>
                        </td>
                        <td class="student-request-type"><?= e($request['type_name']) ?></td>
                        <td><span class="request-status <?= e($statusClass) ?>"><?= e($request['status']) ?></span></td>
                        <td class="student-request-date"><?= e($request['created_at']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (!$requests): ?>
    <p class="muted">You have not submitted any requests.</p>
<?php endif; ?>

<div
    class="request-modal"
    id="request-modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="request-modal-title"
    hidden
>
    <div class="request-modal-content">
        <button
            type="button"
            class="request-modal-close"
            aria-label="Close request details"
        >
            &times;
        </button>

        <h2 id="request-modal-title"></h2>

        <div class="request-modal-meta">
            <p>
                <b>Student</b>
                <span data-modal-field="student"></span>
                <span class="request-modal-email" data-modal-field="email"></span>
            </p>

            <p>
                <b>Request Type</b>
                <span data-modal-field="type"></span>
            </p>

            <p>
                <b>Status</b>
                <span data-modal-field="status"></span>
            </p>

            <p>
                <b>Created</b>
                <span data-modal-field="created"></span>
            </p>
        </div>

        <div class="request-modal-description">
            <b>Description</b>
            <p data-modal-field="description"></p>
        </div>

        <div class="request-modal-description" data-modal-notes-container hidden>
            <b>Staff Notes</b>
            <p data-modal-field="staff-notes"></p>
        </div>
    </div>
</div>

<div
    class="request-modal"
    id="create-request-modal"
    role="dialog"
    aria-modal="true"
    aria-labelledby="create-request-title"
    hidden
>
    <div class="request-modal-content">
        <button
            type="button"
            class="request-modal-close create-request-close"
            aria-label="Close create request form"
        >
            &times;
        </button>

        <h2 id="create-request-title">Create a request</h2>

        <form method="post" action="my_requests.php">
            <div class="request-form-grid">
                <div class="request-form-field">
                    <label for="request-type">
                        Request type
                        <select id="request-type" name="type_id" required>
                            <?php foreach ($types as $type): ?>
                                <option value="<?= (int) $type['id'] ?>"><?= e($type['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>

                <div class="request-form-field">
                    <label for="request-subject">
                        Subject
                        <input id="request-subject" name="subject" maxlength="180" required>
                    </label>
                </div>

                <div class="request-form-field request-form-field-full">
                    <label for="request-description">
                        Description
                        <textarea id="request-description" name="description" rows="7" required></textarea>
                    </label>
                </div>
            </div>

            <div class="request-modal-footer">
                <button type="button" class="button secondary create-request-close">Cancel</button>
                <button type="submit" class="button">Submit request</button>
            </div>
        </form>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>
