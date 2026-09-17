<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('admin');

/*
|--------------------------------------------------------------------------
| Request Report Data
|--------------------------------------------------------------------------
*/

$rows = $pdo
    ->query("
        SELECT 
            status,
            COUNT(*) AS total
        FROM requests
        GROUP BY status
        ORDER BY total DESC
    ")
    ->fetchAll(PDO::FETCH_ASSOC);

/*
|--------------------------------------------------------------------------
| Calculate Report Statistics
|--------------------------------------------------------------------------
*/

$totalRequests = 0;
$topStatus = null;
$topStatusCount = 0;

foreach ($rows as $row) {
    $count = (int) $row['total'];

    $totalRequests += $count;

    if ($count > $topStatusCount) {
        $topStatus = $row['status'];
        $topStatusCount = $count;
    }
}

/*
|--------------------------------------------------------------------------
| Status Helpers
|--------------------------------------------------------------------------
*/

$statusCounts = [];

foreach ($rows as $row) {
    $statusKey = strtolower(trim($row['status']));
    $statusCounts[$statusKey] = (int) $row['total'];
}

$pendingCount = 0;
$completedCount = 0;

/*
| Treat common pending/open statuses as active requests.
*/
foreach (['pending', 'processing', 'in progress', 'open'] as $status) {
    $pendingCount += $statusCounts[$status] ?? 0;
}

/*
| Treat common completed statuses as completed requests.
*/
foreach (['completed', 'complete', 'approved', 'resolved', 'released'] as $status) {
    $completedCount += $statusCounts[$status] ?? 0;
}

/*
|--------------------------------------------------------------------------
| Percentage Helper
|--------------------------------------------------------------------------
*/

function reportPercentage(int $value, int $total): float
{
    if ($total <= 0) {
        return 0;
    }

    return round(($value / $total) * 100, 1);
}

/*
|--------------------------------------------------------------------------
| Automatic Analysis
|--------------------------------------------------------------------------
*/

$analysis = [];

if ($totalRequests === 0) {
    $analysis[] = [
        'type' => 'neutral',
        'title' => 'No request data yet',
        'text' => 'There are currently no student requests recorded in the system. Request activity will appear here once students begin submitting requests.'
    ];
} else {

    if ($topStatus !== null) {
        $topPercentage = reportPercentage($topStatusCount, $totalRequests);

        $analysis[] = [
            'type' => 'primary',
            'title' => 'Most common status',
            'text' => sprintf(
                '%s represents the largest portion of requests with %d request%s (%s%% of the total).',
                ucfirst($topStatus),
                $topStatusCount,
                $topStatusCount === 1 ? '' : 's',
                $topPercentage
            )
        ];
    }

    if ($pendingCount > 0) {
        $pendingPercentage = reportPercentage($pendingCount, $totalRequests);

        $analysis[] = [
            'type' => 'warning',
            'title' => 'Requests requiring attention',
            'text' => sprintf(
                'There are currently %d request%s in pending or active statuses, representing %s%% of all recorded requests.',
                $pendingCount,
                $pendingCount === 1 ? '' : 's',
                $pendingPercentage
            )
        ];
    } else {
        $analysis[] = [
            'type' => 'success',
            'title' => 'No active requests',
            'text' => 'There are currently no requests recorded under the common pending or active statuses.'
        ];
    }

    if ($completedCount > 0) {
        $completedPercentage = reportPercentage($completedCount, $totalRequests);

        $analysis[] = [
            'type' => 'success',
            'title' => 'Completed requests',
            'text' => sprintf(
                '%d request%s %s currently classified under completed, approved, resolved, released, or similar completed statuses (%s%%).',
                $completedCount,
                $completedCount === 1 ? '' : 's',
                $completedCount === 1 ? 'is' : 'are',
                $completedPercentage
            )
        ];
    }

    if ($completedCount === $totalRequests && $totalRequests > 0) {
        $analysis[] = [
            'type' => 'success',
            'title' => 'All requests completed',
            'text' => 'All recorded requests are currently classified under completed-type statuses.'
        ];
    }
}

/*
|--------------------------------------------------------------------------
| Page Setup
|--------------------------------------------------------------------------
*/

$pageTitle = 'Reports';
$basePath = '../';

require __DIR__ . '/../includes/header.php';

?>

<div class="reports-page">

    <!-- Page Heading -->
    <div class="page-heading reports-heading">

        <div>
            <h1>Request Reports</h1>
            <p class="muted">
                Overview and analysis of student request activity by current status.
            </p>
        </div>

        <div class="reports-actions">
            <a class="button secondary button-compact" href="dashboard.php">
                Back to Dashboard
            </a>
        </div>

    </div>


    <!-- Summary Cards -->
    <div class="report-summary-grid">

        <div class="report-summary-card">
            <div class="report-summary-icon">📋</div>

            <div>
                <span class="report-summary-label">Total Requests</span>
                <strong class="report-summary-value">
                    <?= $totalRequests ?>
                </strong>
            </div>
        </div>


        <div class="report-summary-card">
            <div class="report-summary-icon warning">⏳</div>

            <div>
                <span class="report-summary-label">Active / Pending</span>
                <strong class="report-summary-value">
                    <?= $pendingCount ?>
                </strong>
            </div>
        </div>


        <div class="report-summary-card">
            <div class="report-summary-icon success">✓</div>

            <div>
                <span class="report-summary-label">Completed</span>
                <strong class="report-summary-value">
                    <?= $completedCount ?>
                </strong>
            </div>
        </div>


        <div class="report-summary-card">
            <div class="report-summary-icon primary">📊</div>

            <div>
                <span class="report-summary-label">Top Status</span>

                <strong class="report-summary-value report-status-value">
                    <?= $topStatus !== null ? e(ucfirst($topStatus)) : '—' ?>
                </strong>
            </div>
        </div>

    </div>


    <!-- Analysis -->
    <section class="report-analysis-section">

        <div class="section-heading">
            <div>
                <h2>Request Analysis</h2>
                <p class="muted">
                    Automatically generated observations based on the current request data.
                </p>
            </div>
        </div>


        <div class="report-analysis-grid">

            <?php foreach ($analysis as $item): ?>

                <article class="analysis-card analysis-<?= e($item['type']) ?>">

                    <div class="analysis-card-indicator"></div>

                    <div class="analysis-card-content">

                        <h3>
                            <?= e($item['title']) ?>
                        </h3>

                        <p>
                            <?= e($item['text']) ?>
                        </p>

                    </div>

                </article>

            <?php endforeach; ?>

        </div>

    </section>


    <!-- Status Breakdown -->
    <section class="report-breakdown-section">

        <div class="section-heading">

            <div>
                <h2>Status Breakdown</h2>
                <p class="muted">
                    Distribution of all recorded student requests.
                </p>
            </div>

        </div>


        <?php if (!$rows): ?>

            <div class="report-empty">

                <div class="report-empty-icon">
                    📊
                </div>

                <h3>No report data available</h3>

                <p>
                    There are no requests available to analyze yet.
                </p>

                <a class="button primary" href="requests.php">
                    View Requests
                </a>

            </div>

        <?php else: ?>

            <div class="table-wrap report-table-wrap">

                <table class="report-table">

                    <thead>

                        <tr>
                            <th>Status</th>
                            <th>Total Requests</th>
                            <th>Percentage</th>
                            <th>Distribution</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($rows as $row): ?>

                            <?php
                            $status = trim($row['status']);
                            $count = (int) $row['total'];
                            $percentage = reportPercentage($count, $totalRequests);
                            ?>

                            <tr>

                                <td>
                                    <span class="status report-status">
                                        <?= e($status) ?>
                                    </span>
                                </td>


                                <td>
                                    <strong class="report-total">
                                        <?= $count ?>
                                    </strong>
                                </td>


                                <td>
                                    <strong class="report-percentage">
                                        <?= number_format($percentage, 1) ?>%
                                    </strong>
                                </td>


                                <td class="report-distribution-cell">

                                    <div
                                        class="report-progress"
                                        role="progressbar"
                                        aria-valuenow="<?= $percentage ?>"
                                        aria-valuemin="0"
                                        aria-valuemax="100"
                                        aria-label="<?= e($status) ?> <?= $percentage ?> percent"
                                    >
                                        <div
                                            class="report-progress-bar"
                                            style="width: <?= min(100, max(0, $percentage)) ?>%;"
                                        ></div>
                                    </div>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php endif; ?>

    </section>


    <!-- Report Footer -->
    <?php if ($totalRequests > 0): ?>

        <div class="report-footer-note">

            <p>
                This report is based on the current records stored in the
                <strong>requests</strong> table. Percentages are calculated
                from the total number of recorded requests.
            </p>

        </div>

    <?php endif; ?>

</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>