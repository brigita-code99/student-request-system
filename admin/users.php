<?php
require_once __DIR__ . '/../config/database.php';require_once __DIR__.'/../includes/auth.php';requireRole('admin');$users=$pdo->query('SELECT name,email,role,created_at FROM users ORDER BY name')->fetchAll();$pageTitle='Users';$basePath='../';require __DIR__.'/../includes/header.php';
?>
<main class="container users-page">
    <div class="page-heading users-page-heading">
        <div>
            <span class="eyebrow">Directory management</span>
            <h1>Users</h1>
            <p class="muted">Manage accounts and access across the request system.</p>
        </div>
        <div class="page-heading-actions">
            <div class="users-count">
                <strong><?= count($users) ?></strong>
                <span>total accounts</span>
            </div>
            <a class="button secondary button-compact" href="dashboard.php">Back to dashboard</a>
        </div>
    </div>

    <section class="users-table-card" aria-labelledby="users-table-title">
        <div class="users-table-intro">
            <div>
                <span class="eyebrow">Access overview</span>
                <h2 id="users-table-title">System accounts</h2>
            </div>
            <p>Review each account's role and registration date.</p>
        </div>
        <div class="table-wrap responsive-table">
            <table class="users-table">
                <thead><tr><th>Name</th><th>Email address</th><th>Role</th><th>Joined</th></tr></thead>
                <tbody><?php foreach($users as $user):?>
                    <tr>
                        <td data-label="Name">
                            <div class="user-name-cell">
                                <span class="user-avatar" aria-hidden="true"><?= e(strtoupper(substr($user['name'], 0, 1))) ?></span>
                                <strong><?=e($user['name'])?></strong>
                            </div>
                        </td>
                        <td data-label="Email address" class="user-email"><?=e($user['email'])?></td>
                        <td data-label="Role"><span class="status status-<?= e(strtolower($user['role'])) ?>"><?=e(ucfirst($user['role']))?></span></td>
                        <td data-label="Joined" class="user-date"><?=e($user['created_at'])?></td>
                    </tr>
                <?php endforeach;?></tbody>
            </table>
        </div>
    </section>
</main>
<?php require __DIR__.'/../includes/footer.php'; ?>
