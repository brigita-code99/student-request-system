<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('admin');

/*
|--------------------------------------------------------------------------
| Dashboard Statistics
|--------------------------------------------------------------------------
*/

$stats = [
    'users'     => 0,
    'requests'  => 0,
    'pending'   => 0,
    'completed' => 0
];

try {

    $stats['users'] = (int) $pdo
        ->query("SELECT COUNT(*) FROM users")
        ->fetchColumn();

    $stats['requests'] = (int) $pdo
        ->query("SELECT COUNT(*) FROM requests")
        ->fetchColumn();

    $stats['pending'] = (int) $pdo
        ->query("
            SELECT COUNT(*)
            FROM requests
            WHERE status = 'Pending'
        ")
        ->fetchColumn();

    $stats['completed'] = (int) $pdo
        ->query("
            SELECT COUNT(*)
            FROM requests
            WHERE status = 'Resolved'
        ")
        ->fetchColumn();

} catch (PDOException $e) {

    // Keep dashboard usable if a query fails.

}


/*
|--------------------------------------------------------------------------
| Recent Requests
|--------------------------------------------------------------------------
*/

$recentRequests = [];

try {

    $stmt = $pdo->query("
        SELECT
            r.id,
            r.subject,
            t.name AS request_type,
            r.status,
            r.created_at
        FROM requests r
        INNER JOIN request_types t
            ON t.id = r.type_id
        ORDER BY r.created_at DESC
        LIMIT 5
    ");

    $recentRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $recentRequests = [];

}


/*
|--------------------------------------------------------------------------
| Request Status Counts
|--------------------------------------------------------------------------
*/

$statusCounts = [
    'Pending'     => 0,
    'In Progress' => 0,
    'Resolved'    => 0,
    'Rejected'    => 0
];

try {

    $stmt = $pdo->query("
        SELECT
            status,
            COUNT(*) AS total
        FROM requests
        GROUP BY status
    ");

    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {

        $status = $row['status'] ?? '';

        if (isset($statusCounts[$status])) {
            $statusCounts[$status] = (int) $row['total'];
        }

    }

} catch (PDOException $e) {

    // Keep default values.

}


/*
|--------------------------------------------------------------------------
| Page Configuration
|--------------------------------------------------------------------------
*/

$pageTitle = 'Admin Dashboard';
$basePath = '../';
$showBackButton = false;

require __DIR__ . '/../includes/header.php';
?>


<style>

/* =========================================================
   ADMIN DASHBOARD
   SELF-CONTAINED DESIGN
   ========================================================= */

.admin-dashboard {
    width: 100%;
    max-width: 1380px;
    margin: 0 auto;
    padding: 10px 0 40px;
    color: #263b36;
}


/* =========================================================
   DASHBOARD HEADER
   ========================================================= */

.admin-dashboard .admin-welcome {
    position: relative;

    display: flex;
    align-items: center;
    justify-content: space-between;

    gap: 25px;

    margin-bottom: 25px;
    padding: 30px 32px;

    overflow: hidden;

    background:
        radial-gradient(
            circle at 90% 10%,
            rgba(143,216,192,.28),
            transparent 30%
        ),
        linear-gradient(
            135deg,
            #ffffff,
            #f8fdfb
        );

    border: 1px solid #e2eee9;
    border-radius: 22px;

    box-shadow:
        0 10px 30px rgba(52,83,73,.07);
}

.admin-dashboard .admin-welcome::after {
    content: "";

    position: absolute;

    right: -70px;
    bottom: -100px;

    width: 220px;
    height: 220px;

    border: 35px solid rgba(246,165,143,.12);

    border-radius: 50%;

    pointer-events: none;
}

.admin-dashboard .admin-welcome h1 {
    position: relative;
    z-index: 2;

    margin: 0 0 7px;

    color: #263b36;

    font-size: clamp(1.7rem, 3vw, 2.35rem);
    font-weight: 850;

    line-height: 1.1;

    letter-spacing: -.05em;
}

.admin-dashboard .admin-welcome p {
    position: relative;
    z-index: 2;

    margin: 0;

    color: #7b8b86;

    font-size: .9rem;
}

.admin-dashboard .dashboard-date {
    position: relative;
    z-index: 2;

    display: flex;
    align-items: center;
    justify-content: center;

    padding: 11px 16px;

    background: #ffffff;

    border: 1px solid #dcebe5;
    border-radius: 12px;

    color: #40534d;

    font-size: .78rem;
    font-weight: 750;

    white-space: nowrap;

    box-shadow:
        0 5px 15px rgba(52,83,73,.05);
}


/* =========================================================
   STATISTICS
   ========================================================= */

.admin-dashboard .admin-stat-grid {
    display: grid;

    grid-template-columns:
        repeat(4, minmax(0, 1fr));

    gap: 18px;

    margin-bottom: 25px;
}

.admin-dashboard .admin-stat-card {
    position: relative;

    min-height: 160px;

    padding: 21px;

    overflow: hidden;

    background: #ffffff;

    border: 1px solid #e2eee9;
    border-radius: 18px;

    box-shadow:
        0 8px 24px rgba(52,83,73,.06);

    transition:
        transform .2s ease,
        box-shadow .2s ease,
        border-color .2s ease;
}

.admin-dashboard .admin-stat-card:hover {
    transform: translateY(-3px);

    border-color: #cce9df;

    box-shadow:
        0 14px 30px rgba(52,83,73,.11);
}

.admin-dashboard .admin-stat-card::before {
    content: "";

    position: absolute;

    top: 0;
    left: 0;

    width: 100%;
    height: 4px;

    background:
        linear-gradient(
            90deg,
            #55b89d,
            #8fd8c0,
            #f6a58f
        );
}

.admin-dashboard .admin-stat-card::after {
    content: "";

    position: absolute;

    right: -35px;
    bottom: -45px;

    width: 115px;
    height: 115px;

    border-radius: 50%;

    background: #fff0eb;

    opacity: .8;
}

.admin-dashboard .stat-top {
    position: relative;
    z-index: 2;

    margin-bottom: 15px;
}

.admin-dashboard .stat-icon {
    display: inline-flex;

    align-items: center;
    justify-content: center;

    width: 44px;
    height: 44px;

    border-radius: 13px;

    background: #eaf9f4;

    color: #55b89d;

    font-size: 18px;
    font-weight: 800;
}

.admin-dashboard .stat-label {
    position: relative;
    z-index: 2;

    display: block;

    margin-bottom: 5px;

    color: #7b8b86;

    font-size: .72rem;
    font-weight: 750;

    text-transform: uppercase;
    letter-spacing: .05em;
}

.admin-dashboard .stat-value {
    position: relative;
    z-index: 2;

    display: block;

    color: #263b36;

    font-size: 1.9rem;
    font-weight: 850;

    line-height: 1.1;

    letter-spacing: -.05em;
}

.admin-dashboard .stat-description {
    position: relative;
    z-index: 2;

    margin-top: 7px;

    color: #9aa9a4;

    font-size: .69rem;
}


/* =========================================================
   MAIN GRID
   ========================================================= */

.admin-dashboard .dashboard-grid {
    display: grid;

    grid-template-columns:
        minmax(0, 1.65fr)
        minmax(300px, .85fr);

    gap: 20px;

    margin-bottom: 30px;
}


/* =========================================================
   PANELS
   ========================================================= */

.admin-dashboard .dashboard-panel {
    min-width: 0;

    overflow: hidden;

    background: #ffffff;

    border: 1px solid #e2eee9;
    border-radius: 18px;

    box-shadow:
        0 8px 25px rgba(52,83,73,.055);
}

.admin-dashboard .panel-header {
    display: flex;

    align-items: center;
    justify-content: space-between;

    gap: 15px;

    padding: 19px 21px;

    background: #ffffff;

    border-bottom: 1px solid #e5eee9;
}

.admin-dashboard .panel-header h2 {
    margin: 0;

    color: #263b36;

    font-size: 1rem;
    font-weight: 800;
}

.admin-dashboard .panel-link {
    padding: 6px 9px;

    border-radius: 8px;

    color: #55b89d;

    font-size: .72rem;
    font-weight: 800;

    text-decoration: none;

    transition: .2s ease;
}

.admin-dashboard .panel-link:hover {
    background: #eaf9f4;

    color: #e88972;

    text-decoration: none;
}


/* =========================================================
   REQUEST TABLE
   ========================================================= */

.admin-dashboard .request-table-wrapper {
    width: 100%;

    overflow-x: auto;
}

.admin-dashboard .request-table {
    width: 100%;

    min-width: 560px;

    border-collapse: collapse;

    background: #ffffff;
}

.admin-dashboard .request-table th {
    padding: 12px 18px;

    background: #f3faf7;

    border-bottom: 1px solid #e5eee9;

    color: #58786e;

    font-size: .64rem;
    font-weight: 800;

    text-align: left;

    text-transform: uppercase;

    letter-spacing: .07em;
}

.admin-dashboard .request-table td {
    padding: 15px 18px;

    border-bottom: 1px solid #edf3f0;

    color: #40534d;

    font-size: .78rem;

    vertical-align: middle;
}

.admin-dashboard .request-table tbody tr {
    transition: background .15s ease;
}

.admin-dashboard .request-table tbody tr:hover {
    background: #fffaf8;
}

.admin-dashboard .request-table tbody tr:last-child td {
    border-bottom: 0;
}

.admin-dashboard .request-table th:nth-child(1),
.admin-dashboard .request-table td:nth-child(1) {
    width: 75px;
}

.admin-dashboard .request-table th:nth-child(3),
.admin-dashboard .request-table td:nth-child(3) {
    width: 125px;
}

.admin-dashboard .request-table th:nth-child(4),
.admin-dashboard .request-table td:nth-child(4) {
    width: 115px;
}

.admin-dashboard .request-id {
    color: #55b89d;

    font-weight: 800;
}

.admin-dashboard .request-type {
    color: #263b36;

    font-weight: 700;
}

.admin-dashboard .request-date {
    color: #7b8b86;

    white-space: nowrap;
}


/* =========================================================
   STATUS BADGES
   ========================================================= */

.admin-dashboard .status-badge {
    display: inline-flex;

    align-items: center;
    justify-content: center;

    padding: 5px 9px;

    border-radius: 999px;

    font-size: .66rem;
    font-weight: 800;

    white-space: nowrap;
}

.admin-dashboard .status-pending {
    background: #fff3e7;

    color: #c27a28;

    border: 1px solid #f5dfc5;
}

.admin-dashboard .status-processing {
    background: #eaf9f4;

    color: #3e927b;

    border: 1px solid #d0eee3;
}

.admin-dashboard .status-completed {
    background: #e8f8f1;

    color: #398b73;

    border: 1px solid #d2eee2;
}

.admin-dashboard .status-rejected {
    background: #fff0f0;

    color: #c26767;

    border: 1px solid #f1d7d7;
}

.admin-dashboard .status-default {
    background: #f1f5f3;

    color: #667570;

    border: 1px solid #e2e9e6;
}


/* =========================================================
   STATUS SUMMARY
   ========================================================= */

.admin-dashboard .status-summary {
    padding: 22px;
}

.admin-dashboard .status-item {
    margin-bottom: 23px;
}

.admin-dashboard .status-item:last-child {
    margin-bottom: 0;
}

.admin-dashboard .status-info {
    display: flex;

    align-items: center;
    justify-content: space-between;

    gap: 15px;

    margin-bottom: 8px;
}

.admin-dashboard .status-info span {
    color: #40534d;

    font-size: .78rem;
    font-weight: 650;
}

.admin-dashboard .status-info strong {
    color: #263b36;

    font-size: .78rem;
    font-weight: 850;
}

.admin-dashboard .progress-bar {
    width: 100%;
    height: 8px;

    overflow: hidden;

    background: #edf4f1;

    border-radius: 999px;
}

.admin-dashboard .progress-value {
    width: var(--progress-width, 0%);

    height: 100%;

    background:
        linear-gradient(
            90deg,
            #55b89d,
            #8fd8c0,
            #f6a58f
        );

    border-radius: 999px;

    transition: width .4s ease;
}


/* =========================================================
   EMPTY STATE
   ========================================================= */

.admin-dashboard .empty-state {
    display: flex;

    align-items: center;
    justify-content: center;

    flex-direction: column;

    min-height: 245px;

    padding: 35px 20px;

    text-align: center;
}

.admin-dashboard .empty-state-icon {
    display: grid;

    place-items: center;

    width: 55px;
    height: 55px;

    margin-bottom: 14px;

    border-radius: 16px;

    background: #eaf9f4;

    color: #55b89d;

    font-size: 25px;
}

.admin-dashboard .empty-state strong {
    display: block;

    margin-bottom: 4px;

    color: #40534d;

    font-size: .86rem;
}

.admin-dashboard .empty-state small {
    color: #7b8b86;

    font-size: .73rem;
}


/* =========================================================
   QUICK ACTIONS
   ========================================================= */

.admin-dashboard .quick-actions-title {
    margin: 0 0 15px;

    color: #263b36;

    font-size: 1rem;
    font-weight: 800;
}

.admin-dashboard .admin-actions {
    display: grid;

    grid-template-columns:
        repeat(4, minmax(0, 1fr));

    gap: 15px;

    margin-bottom: 25px;
}

.admin-dashboard .admin-action {
    position: relative;

    display: flex;

    align-items: center;

    min-height: 90px;

    gap: 13px;

    padding: 16px;

    overflow: hidden;

    background: #ffffff;

    border: 1px solid #e2eee9;
    border-radius: 16px;

    color: inherit;

    text-decoration: none;

    box-shadow:
        0 6px 20px rgba(52,83,73,.045);

    transition:
        transform .2s ease,
        border-color .2s ease,
        box-shadow .2s ease;
}

.admin-dashboard .admin-action::after {
    content: "";

    position: absolute;

    right: -25px;
    bottom: -35px;

    width: 75px;
    height: 75px;

    border-radius: 50%;

    background: #fff0eb;
}

.admin-dashboard .admin-action:hover {
    transform: translateY(-3px);

    border-color: #cce9df;

    box-shadow:
        0 13px 28px rgba(52,83,73,.09);
}

.admin-dashboard .admin-action .action-icon {
    position: relative;
    z-index: 2;

    display: grid;

    place-items: center;

    width: 43px;
    height: 43px;

    flex: 0 0 43px;

    border-radius: 12px;

    background: #eaf9f4;

    color: #55b89d;

    font-size: 18px;
    font-weight: 850;
}

.admin-dashboard .admin-action > span:nth-child(2) {
    position: relative;
    z-index: 2;

    min-width: 0;

    flex: 1;
}

.admin-dashboard .admin-action strong {
    display: block;

    margin-bottom: 4px;

    color: #263b36;

    font-size: .78rem;
    font-weight: 800;
}

.admin-dashboard .admin-action small {
    display: block;

    color: #7b8b86;

    font-size: .66rem;

    line-height: 1.4;
}

.admin-dashboard .admin-action .action-arrow {
    position: relative;
    z-index: 2;

    margin-left: auto;

    color: #e88972;

    font-size: 1.05rem;
    font-weight: 850;

    transition: transform .2s ease;
}

.admin-dashboard .admin-action:hover .action-arrow {
    transform: translateX(4px);
}


/* Different action icon colors */

.admin-dashboard .admin-action:nth-child(1) .action-icon {
    background: #eaf9f4;
    color: #55b89d;
}

.admin-dashboard .admin-action:nth-child(2) .action-icon {
    background: #fff0eb;
    color: #e88972;
}

.admin-dashboard .admin-action:nth-child(3) .action-icon {
    background: #eef8f5;
    color: #4d9d88;
}

.admin-dashboard .admin-action:nth-child(4) .action-icon {
    background: #fff4e8;
    color: #c88438;
}


/* =========================================================
   RESPONSIVE — TABLET
   ========================================================= */

@media (max-width: 1100px) {

    .admin-dashboard .admin-stat-grid {
        grid-template-columns:
            repeat(2, minmax(0, 1fr));
    }

    .admin-dashboard .dashboard-grid {
        grid-template-columns: 1fr;
    }

    .admin-dashboard .admin-actions {
        grid-template-columns:
            repeat(2, minmax(0, 1fr));
    }

}


/* =========================================================
   RESPONSIVE — MOBILE
   ========================================================= */

@media (max-width: 760px) {

    .admin-dashboard {
        padding-top: 0;
    }

    .admin-dashboard .admin-welcome {
        align-items: flex-start;

        flex-direction: column;

        padding: 23px 20px;

        border-radius: 18px;
    }

    .admin-dashboard .admin-welcome h1 {
        font-size: 1.65rem;
    }

    .admin-dashboard .admin-welcome p {
        font-size: .8rem;
    }

    .admin-dashboard .dashboard-date {
        width: 100%;
    }


    /* Stats */

    .admin-dashboard .admin-stat-grid {
        grid-template-columns: 1fr;

        gap: 13px;
    }

    .admin-dashboard .admin-stat-card {
        min-height: 145px;
    }


    /* Main panels */

    .admin-dashboard .dashboard-grid {
        gap: 15px;
    }

    .admin-dashboard .dashboard-panel {
        border-radius: 16px;
    }

    .admin-dashboard .panel-header {
        padding: 17px 18px;
    }


    /* Table */

    .admin-dashboard .request-table {
        min-width: 560px;
    }


    /* Quick actions */

    .admin-dashboard .admin-actions {
        grid-template-columns: 1fr;

        gap: 12px;
    }

    .admin-dashboard .admin-action {
        min-height: 82px;
    }

}


/* =========================================================
   RESPONSIVE — SMALL PHONE
   ========================================================= */

@media (max-width: 480px) {

    .admin-dashboard .admin-welcome {
        padding: 20px 17px;
    }

    .admin-dashboard .admin-welcome h1 {
        font-size: 1.5rem;
    }

    .admin-dashboard .admin-stat-card {
        min-height: 138px;

        padding: 17px;
    }

    .admin-dashboard .stat-value {
        font-size: 1.65rem;
    }

    .admin-dashboard .stat-icon {
        width: 40px;
        height: 40px;
    }

    .admin-dashboard .panel-header {
        padding: 16px;
    }

    .admin-dashboard .status-summary {
        padding: 17px;
    }

    .admin-dashboard .admin-action {
        padding: 14px;
    }

}

</style>


<div class="admin-dashboard">

    <!-- =====================================================
         WELCOME HEADER
         ===================================================== -->

    <div class="admin-welcome">

        <div>

            <h1>
                Admin Dashboard
            </h1>

            <p>
                Welcome back. Here's an overview of your request management system.
            </p>

        </div>

        <div class="dashboard-date">
            <?= e(date('F d, Y')) ?>
        </div>

    </div>


    <!-- =====================================================
         STATISTICS
         ===================================================== -->

    <div class="admin-stat-grid">

        <!-- Users -->

        <div class="admin-stat-card">

            <div class="stat-top">
                <span class="stat-icon">◉</span>
            </div>

            <span class="stat-label">
                Total Users
            </span>

            <strong class="stat-value">
                <?= number_format($stats['users']) ?>
            </strong>

            <div class="stat-description">
                Registered system accounts
            </div>

        </div>


        <!-- Requests -->

        <div class="admin-stat-card">

            <div class="stat-top">
                <span class="stat-icon">▤</span>
            </div>

            <span class="stat-label">
                Total Requests
            </span>

            <strong class="stat-value">
                <?= number_format($stats['requests']) ?>
            </strong>

            <div class="stat-description">
                All submitted requests
            </div>

        </div>


        <!-- Pending -->

        <div class="admin-stat-card">

            <div class="stat-top">
                <span class="stat-icon">◷</span>
            </div>

            <span class="stat-label">
                Pending Requests
            </span>

            <strong class="stat-value">
                <?= number_format($stats['pending']) ?>
            </strong>

            <div class="stat-description">
                Requests waiting for action
            </div>

        </div>


        <!-- Resolved -->

        <div class="admin-stat-card">

            <div class="stat-top">
                <span class="stat-icon">✓</span>
            </div>

            <span class="stat-label">
                Resolved Requests
            </span>

            <strong class="stat-value">
                <?= number_format($stats['completed']) ?>
            </strong>

            <div class="stat-description">
                Successfully completed
            </div>

        </div>

    </div>


    <!-- =====================================================
         RECENT REQUESTS + STATUS
         ===================================================== -->

    <div class="dashboard-grid">


        <!-- Recent Requests -->

        <section class="dashboard-panel">

            <div class="panel-header">

                <h2>
                    Recent Requests
                </h2>

                <a
                    href="requests.php"
                    class="panel-link"
                >
                    View All →
                </a>

            </div>


            <?php if (!empty($recentRequests)): ?>

                <div class="request-table-wrapper">

                    <table class="request-table">

                        <thead>

                            <tr>

                                <th>
                                    ID
                                </th>

                                <th>
                                    Request Type
                                </th>

                                <th>
                                    Date
                                </th>

                                <th>
                                    Status
                                </th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php foreach ($recentRequests as $request): ?>

                                <?php

                                $status = $request['status'] ?? 'Unknown';

                                $statusClass = match ($status) {

                                    'Pending'
                                        => 'status-pending',

                                    'In Progress'
                                        => 'status-processing',

                                    'Resolved'
                                        => 'status-completed',

                                    'Rejected'
                                        => 'status-rejected',

                                    default
                                        => 'status-default'

                                };

                                ?>

                                <tr>

                                    <td class="request-id">
                                        #<?= (int) $request['id'] ?>
                                    </td>

                                    <td class="request-type">

                                        <?= e(
                                            $request['request_type']
                                                ?? 'Request'
                                        ) ?>

                                    </td>

                                    <td class="request-date">

                                        <?=
                                            !empty($request['created_at'])
                                                ? e(
                                                    date(
                                                        'M d, Y',
                                                        strtotime(
                                                            $request['created_at']
                                                        )
                                                    )
                                                )
                                                : '—'
                                        ?>

                                    </td>

                                    <td>

                                        <span
                                            class="status-badge <?= $statusClass ?>"
                                        >
                                            <?= e($status) ?>
                                        </span>

                                    </td>

                                </tr>

                            <?php endforeach; ?>

                        </tbody>

                    </table>

                </div>

            <?php else: ?>

                <div class="empty-state">

                    <div class="empty-state-icon">
                        ▤
                    </div>

                    <strong>
                        No requests yet
                    </strong>

                    <small>
                        Submitted requests will appear here.
                    </small>

                </div>

            <?php endif; ?>

        </section>


        <!-- =================================================
             REQUEST STATUS
             ================================================= -->

        <section class="dashboard-panel">

            <div class="panel-header">

                <h2>
                    Request Status
                </h2>

            </div>


            <div class="status-summary">

                <?php

                $totalForProgress =
                    max($stats['requests'], 1);

                ?>


                <?php foreach ($statusCounts as $status => $count): ?>

                    <?php

                    $percentage = min(
                        100,
                        round(
                            ($count / $totalForProgress) * 100
                        )
                    );

                    ?>

                    <div class="status-item">

                        <div class="status-info">

                            <span>
                                <?= e($status) ?>
                            </span>

                            <strong>
                                <?= number_format($count) ?>
                            </strong>

                        </div>

                        <div class="progress-bar">

                            <div
                                class="progress-value"
                                style="--progress-width: <?= $percentage ?>%;"
                                role="progressbar"
                                aria-label="<?= e($status) ?> requests"
                                aria-valuenow="<?= $percentage ?>"
                                aria-valuemin="0"
                                aria-valuemax="100"
                            ></div>

                        </div>

                    </div>

                <?php endforeach; ?>

            </div>

        </section>

    </div>


    <!-- =====================================================
         QUICK ACTIONS
         ===================================================== -->

    <h2 class="quick-actions-title">
        Quick Actions
    </h2>


    <div class="admin-actions">


        <!-- Requests -->

        <a
            href="requests.php"
            class="admin-action"
        >

            <span class="action-icon">
                ▤
            </span>

            <span>

                <strong>
                    Manage Requests
                </strong>

                <small>
                    Review and process requests.
                </small>

            </span>

            <span class="action-arrow">
                →
            </span>

        </a>


        <!-- Users -->

        <a
            href="users.php"
            class="admin-action"
        >

            <span class="action-icon">
                ◉
            </span>

            <span>

                <strong>
                    Manage Users
                </strong>

                <small>
                    Manage accounts and roles.
                </small>

            </span>

            <span class="action-arrow">
                →
            </span>

        </a>


        <!-- Request Types -->

        <a
            href="request_types.php"
            class="admin-action"
        >

            <span class="action-icon">
                +
            </span>

            <span>

                <strong>
                    Request Types
                </strong>

                <small>
                    Manage request categories.
                </small>

            </span>

            <span class="action-arrow">
                →
            </span>

        </a>


        <!-- Reports -->

        <a
            href="reports.php"
            class="admin-action"
        >

            <span class="action-icon">
                ◒
            </span>

            <span>

                <strong>
                    Reports
                </strong>

                <small>
                    View request statistics.
                </small>

            </span>

            <span class="action-arrow">
                →
            </span>

        </a>

    </div>

</div>


<?php require __DIR__ . '/../includes/footer.php'; ?>