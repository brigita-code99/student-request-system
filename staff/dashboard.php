<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('staff');

/*
|--------------------------------------------------------------------------
| Dashboard Statistics
|--------------------------------------------------------------------------
*/

$counts = $pdo
    ->query("
        SELECT 
            status,
            COUNT(*) AS total
        FROM requests
        GROUP BY status
    ")
    ->fetchAll(PDO::FETCH_KEY_PAIR);

/*
|--------------------------------------------------------------------------
| Normalize Status Counts
|--------------------------------------------------------------------------
*/

$pendingCount = (int) ($counts['Pending'] ?? 0);
$inProgressCount = (int) ($counts['In Progress'] ?? 0);
$resolvedCount = (int) ($counts['Resolved'] ?? 0);
$rejectedCount = (int) ($counts['Rejected'] ?? 0);

$totalRequests =
    $pendingCount +
    $inProgressCount +
    $resolvedCount +
    $rejectedCount;

/*
|--------------------------------------------------------------------------
| Active Requests
|--------------------------------------------------------------------------
*/

$activeRequests = $pendingCount + $inProgressCount;

/*
|--------------------------------------------------------------------------
| Recent Requests
|--------------------------------------------------------------------------
*/

$recentRequests = $pdo
    ->query("
        SELECT
            r.id,
            r.subject,
            r.status,
            r.created_at,
            u.name AS student_name,
            rt.name AS request_type
        FROM requests r
        LEFT JOIN users u
            ON u.id = r.student_id
        LEFT JOIN request_types rt
            ON rt.id = r.type_id
        ORDER BY r.created_at DESC
        LIMIT 5
    ")
    ->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Page Setup
|--------------------------------------------------------------------------
*/

$pageTitle = 'Staff Dashboard';
$basePath = '../';
$showBackButton = false;

require __DIR__ . '/../includes/header.php';

?>

<div class="staff-dashboard">

    <!-- =====================================================
         PAGE HEADER
         ===================================================== -->

    <div class="page-heading staff-dashboard-heading">

        <div>
            <h1>Staff Dashboard</h1>

            <p class="muted">
                Review, update, and resolve student requests.
            </p>
        </div>

        <a class="button primary" href="requests.php">
            Manage Requests
        </a>

    </div>


    <!-- =====================================================
         SUMMARY
         ===================================================== -->

    <div class="staff-summary">

        <div class="staff-summary-card total-card">

            <div class="staff-summary-icon">
                📋
            </div>

            <div>
                <span>Total Requests</span>
                <strong><?= $totalRequests ?></strong>
            </div>

        </div>


        <div class="staff-summary-card pending-card">

            <div class="staff-summary-icon">
                ⏳
            </div>

            <div>
                <span>Pending</span>
                <strong><?= $pendingCount ?></strong>
            </div>

        </div>


        <div class="staff-summary-card progress-card">

            <div class="staff-summary-icon">
                ↻
            </div>

            <div>
                <span>In Progress</span>
                <strong><?= $inProgressCount ?></strong>
            </div>

        </div>


        <div class="staff-summary-card resolved-card">

            <div class="staff-summary-icon">
                ✓
            </div>

            <div>
                <span>Resolved</span>
                <strong><?= $resolvedCount ?></strong>
            </div>

        </div>

    </div>


    <!-- =====================================================
         DASHBOARD CONTENT
         ===================================================== -->

    <div class="staff-dashboard-grid">


        <!-- =================================================
             REQUEST OVERVIEW
             ================================================= -->

        <section class="staff-panel">

            <div class="staff-panel-heading">

                <div>
                    <h2>Request Overview</h2>

                    <p class="muted">
                        Current workload based on request status.
                    </p>
                </div>

            </div>


            <div class="staff-overview-list">

                <div class="staff-overview-row">

                    <div class="staff-overview-label">

                        <span class="overview-dot pending-dot"></span>

                        <span>Pending</span>

                    </div>

                    <strong><?= $pendingCount ?></strong>

                </div>


                <div class="staff-overview-row">

                    <div class="staff-overview-label">

                        <span class="overview-dot progress-dot"></span>

                        <span>In Progress</span>

                    </div>

                    <strong><?= $inProgressCount ?></strong>

                </div>


                <div class="staff-overview-row">

                    <div class="staff-overview-label">

                        <span class="overview-dot resolved-dot"></span>

                        <span>Resolved</span>

                    </div>

                    <strong><?= $resolvedCount ?></strong>

                </div>


                <div class="staff-overview-row">

                    <div class="staff-overview-label">

                        <span class="overview-dot rejected-dot"></span>

                        <span>Rejected</span>

                    </div>

                    <strong><?= $rejectedCount ?></strong>

                </div>

            </div>


            <div class="staff-active-box">

                <div>
                    <span>Active workload</span>
                    <strong><?= $activeRequests ?></strong>
                </div>

                <a href="requests.php">
                    Open queue →
                </a>

            </div>

        </section>


        <!-- =================================================
             QUICK ACTION
             ================================================= -->

        <section class="staff-panel staff-quick-panel">

            <div class="staff-panel-heading">

                <div>
                    <h2>Quick Actions</h2>

                    <p class="muted">
                        Frequently used staff functions.
                    </p>
                </div>

            </div>


            <a class="staff-action-card" href="requests.php">

                <span class="staff-action-icon">
                    ✓
                </span>

                <span class="staff-action-content">

                    <strong>Manage Requests</strong>

                    <small>
                        Review requests and update their current status.
                    </small>

                </span>

                <span class="staff-action-arrow">
                    →
                </span>

            </a>


            <div class="staff-action-note">

                <span>💡</span>

                <p>
                    Keep request statuses updated so students can easily
                    track the progress of their submissions.
                </p>

            </div>

        </section>

    </div>


    <!-- =====================================================
         RECENT REQUESTS
         ===================================================== -->

    <section class="staff-panel staff-recent-panel">

        <div class="staff-panel-heading">

            <div>
                <h2>Recent Requests</h2>

                <p class="muted">
                    The latest student requests submitted to the system.
                </p>
            </div>

            <a class="button secondary button-compact" href="requests.php">
                View All
            </a>

        </div>


        <?php if (!$recentRequests): ?>

            <div class="staff-empty">

                <div class="staff-empty-icon">
                    📭
                </div>

                <h3>No requests yet</h3>

                <p>
                    Student requests will appear here once they are submitted.
                </p>

            </div>

        <?php else: ?>

            <div class="staff-recent-table-wrap">

                <table class="staff-recent-table">

                    <thead>

                        <tr>
                            <th>Student</th>
                            <th>Request</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Date</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($recentRequests as $request): ?>

                            <tr>

                                <td>
                                    <strong class="staff-student-name">
                                        <?= e($request['student_name'] ?? 'Unknown Student') ?>
                                    </strong>
                                </td>


                                <td>
                                    <?= e($request['subject'] ?? 'Untitled Request') ?>
                                </td>


                                <td>
                                    <?= e($request['request_type'] ?? '—') ?>
                                </td>


                                <td>

                                    <span class="staff-status status-<?= e(strtolower(str_replace(' ', '-', $request['status']))) ?>">
                                        <?= e($request['status']) ?>
                                    </span>

                                </td>


                                <td class="staff-request-date">

                                    <?= !empty($request['created_at'])
                                        ? e(date('M d, Y', strtotime($request['created_at'])))
                                        : '—'
                                    ?>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php endif; ?>

    </section>


    <!-- =====================================================
         DASHBOARD FOOTER NOTE
         ===================================================== -->

    <div class="staff-dashboard-note">

        <span>ℹ</span>

        <p>
            Use the request queue to review student submissions,
            update statuses, and add staff notes when necessary.
        </p>

    </div>

</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>