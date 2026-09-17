<?php
require_once __DIR__ . '/includes/auth.php';
if (isset($_SESSION['role'])) {
    redirectForRole($_SESSION['role']);
}
$pageTitle = 'Log in';
$basePath = '';
$showBackButton = false;
require __DIR__ . '/includes/header.php';
?>
<section class="auth-card">
    <div class="login-visual">
        <div class="login-mark">SR</div>
        <span>Student services portal</span>
        <h1>Everything you need,<br><em>in one place.</em></h1>
        <p>Submit requests, follow updates, and stay connected with your school support team.</p>
        <div class="login-points"><span>✓ Simple request tracking</span><span>✓ Clear status updates</span></div>
    </div>
    <div class="login-form">
        <div class="login-title"><span class="eyebrow">WELCOME BACK</span><h2>Sign in to continue</h2><p class="muted">Access your student request dashboard.</p></div>
        <?php if (isset($_GET['error'])): ?><div class="alert error"><?= e($_GET['error']) ?></div><?php endif; ?>
        <form method="post" action="authenticate.php">
            <label for="email">Email address<input id="email" type="email" name="email" placeholder="you@example.com" required autofocus></label>
            <label for="password">Password<input id="password" type="password" name="password" placeholder="Enter your password" required></label>
            <button class="button login-submit" type="submit">Sign in <span>→</span></button>
        </form>
        <details class="demo-access"><summary>Demo account access</summary><p>Student: student@example.com / student123</p><p>Staff: staff@example.com / staff123</p><p>Admin: admin@example.com / admin123</p></details>
    </div>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>