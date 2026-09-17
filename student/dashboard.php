<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('student');

$userId = (int) ($_SESSION['user_id'] ?? 0);

/*
|--------------------------------------------------------------------------
| Request Statistics
|--------------------------------------------------------------------------
*/

$stats = [
    'total'       => 0,
    'pending'     => 0,
    'in_progress' => 0,
    'resolved'    => 0,
    'rejected'    => 0
];

$statement = $pdo->prepare(
    "SELECT status, COUNT(*) AS total
     FROM requests
     WHERE student_id = ?
     GROUP BY status"
);

$statement->execute([$userId]);

foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $row) {

    $total = (int) $row['total'];

    $stats['total'] += $total;

    switch ($row['status']) {

        case 'Pending':
            $stats['pending'] = $total;
            break;

        case 'In Progress':
            $stats['in_progress'] = $total;
            break;

        case 'Resolved':
            $stats['resolved'] = $total;
            break;

        case 'Rejected':
            $stats['rejected'] = $total;
            break;
    }
}


/*
|--------------------------------------------------------------------------
| Recent Requests
|--------------------------------------------------------------------------
*/

$statement = $pdo->prepare(
    "SELECT
        r.id,
        r.subject,
        r.status,
        r.created_at,
        t.name AS type_name
     FROM requests r
     LEFT JOIN request_types t
        ON t.id = r.type_id
     WHERE r.student_id = ?
     ORDER BY r.created_at DESC
     LIMIT 5"
);

$statement->execute([$userId]);

$recentRequests = $statement->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Page Configuration
|--------------------------------------------------------------------------
*/

$pageTitle = 'Student Dashboard';
$basePath = '../';
$showBackButton = false;

require __DIR__ . '/../includes/header.php';

$types = $pdo->query('SELECT * FROM request_types ORDER BY name')->fetchAll();

?>

<div class="student-dashboard">

    <!-- Dashboard Header -->
    <div class="student-welcome">

        <div class="student-welcome-content">

            <span class="student-welcome-label">
                STUDENT PORTAL
            </span>

            <h1>
                Student Dashboard
            </h1>

            <p>
                Manage your requests and keep track of their progress.
            </p>

        </div>

        <button
            type="button"
            class="student-new-request-btn open-create-request"
        >
            <span class="student-btn-icon">+</span>
            <span>New Request</span>
        </button>

    </div>


    <!-- Statistics -->
    <div class="student-stats">

        <div class="student-stat-card student-stat-total">

            <div class="student-stat-icon">
                ▤
            </div>

            <div class="student-stat-info">

                <span class="student-stat-label">
                    Total Requests
                </span>

                <strong>
                    <?= $stats['total'] ?>
                </strong>

                <small>
                    All submitted requests
                </small>

            </div>

        </div>


        <div class="student-stat-card student-stat-pending">

            <div class="student-stat-icon">
                ◷
            </div>

            <div class="student-stat-info">

                <span class="student-stat-label">
                    Pending
                </span>

                <strong>
                    <?= $stats['pending'] ?>
                </strong>

                <small>
                    Waiting for review
                </small>

            </div>

        </div>


        <div class="student-stat-card student-stat-progress">

            <div class="student-stat-icon">
                ↻
            </div>

            <div class="student-stat-info">

                <span class="student-stat-label">
                    In Progress
                </span>

                <strong>
                    <?= $stats['in_progress'] ?>
                </strong>

                <small>
                    Currently processing
                </small>

            </div>

        </div>


        <div class="student-stat-card student-stat-resolved">

            <div class="student-stat-icon">
                ✓
            </div>

            <div class="student-stat-info">

                <span class="student-stat-label">
                    Resolved
                </span>

                <strong>
                    <?= $stats['resolved'] ?>
                </strong>

                <small>
                    Completed requests
                </small>

            </div>

        </div>

    </div>


    <!-- Main Content -->
    <div class="student-main-grid">


        <!-- Quick Actions -->
        <section class="student-dashboard-card">

            <div class="student-card-header">

                <div>

                    <span class="student-section-label">
                        QUICK ACTIONS
                    </span>

                    <h2>
                        Request Center
                    </h2>

                    <p>
                        Quickly access your request tools.
                    </p>

                </div>

            </div>


            <div class="student-action-list">

                <button
                    type="button"
                    class="student-action-box student-action-create open-create-request"
                >

                    <div class="student-action-icon">
                        +
                    </div>

                    <div class="student-action-text">

                        <strong>
                            Create a Request
                        </strong>

                        <span>
                            Submit a new concern or request.
                        </span>

                    </div>

                    <div class="student-action-arrow">
                        →
                    </div>

                </button>


                <a
                    href="my_requests.php"
                    class="student-action-box student-action-view"
                >

                    <div class="student-action-icon">
                        ▤
                    </div>

                    <div class="student-action-text">

                        <strong>
                            My Requests
                        </strong>

                        <span>
                            View and track your submitted requests.
                        </span>

                    </div>

                    <div class="student-action-arrow">
                        →
                    </div>

                </a>

            </div>

        </section>


        <!-- Status Overview -->
        <section class="student-dashboard-card">

            <div class="student-card-header">

                <div>

                    <span class="student-section-label">
                        OVERVIEW
                    </span>

                    <h2>
                        Request Status
                    </h2>

                    <p>
                        Current status of your requests.
                    </p>

                </div>

            </div>


            <div class="student-status-list">

                <div class="student-status-item">

                    <span class="student-status-indicator pending"></span>

                    <div class="student-status-details">

                        <strong>
                            Pending
                        </strong>

                        <small>
                            Awaiting staff review
                        </small>

                    </div>

                    <b>
                        <?= $stats['pending'] ?>
                    </b>

                </div>


                <div class="student-status-item">

                    <span class="student-status-indicator progress"></span>

                    <div class="student-status-details">

                        <strong>
                            In Progress
                        </strong>

                        <small>
                            Being processed
                        </small>

                    </div>

                    <b>
                        <?= $stats['in_progress'] ?>
                    </b>

                </div>


                <div class="student-status-item">

                    <span class="student-status-indicator resolved"></span>

                    <div class="student-status-details">

                        <strong>
                            Resolved
                        </strong>

                        <small>
                            Successfully completed
                        </small>

                    </div>

                    <b>
                        <?= $stats['resolved'] ?>
                    </b>

                </div>


                <div class="student-status-item">

                    <span class="student-status-indicator rejected"></span>

                    <div class="student-status-details">

                        <strong>
                            Rejected
                        </strong>

                        <small>
                            Request was rejected
                        </small>

                    </div>

                    <b>
                        <?= $stats['rejected'] ?>
                    </b>

                </div>

            </div>

        </section>

    </div>


    <!-- Recent Requests -->
    <section class="student-dashboard-card student-recent-card">

        <div class="student-card-header student-recent-header">

            <div>

                <span class="student-section-label">
                    RECENT ACTIVITY
                </span>

                <h2>
                    My Recent Requests
                </h2>

                <p>
                    Your latest submitted requests.
                </p>

            </div>

            <a
                href="my_requests.php"
                class="student-view-all"
            >
                View All →
            </a>

        </div>


        <?php if (empty($recentRequests)): ?>

            <div class="student-empty">

                <div class="student-empty-icon">
                    ▤
                </div>

                <h3>
                    No requests yet
                </h3>

                <p>
                    You haven't submitted any requests.
                    Create a request when you need assistance.
                </p>

                <button
                    type="button"
                    class="student-new-request-btn student-empty-btn open-create-request"
                >
                    <span>+</span>
                    Create Request
                </button>

            </div>

        <?php else: ?>

            <div class="student-table-container">

                <table class="student-request-table">

                    <thead>

                        <tr>
                            <th>Request</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Date Submitted</th>
                        </tr>

                    </thead>

                    <tbody>

                        <?php foreach ($recentRequests as $request): ?>

                            <?php
                            $statusClass = strtolower(
                                str_replace(
                                    ' ',
                                    '-',
                                    $request['status']
                                )
                            );
                            ?>

                            <tr>

                                <td>

                                    <div class="student-request-name">

                                        <strong>
                                            <?= e($request['subject']) ?>
                                        </strong>

                                        <span>
                                            Request #<?= (int) $request['id'] ?>
                                        </span>

                                    </div>

                                </td>


                                <td>

                                    <span class="student-type">
                                        <?= e(
                                            $request['type_name'] ?? 'General'
                                        ) ?>
                                    </span>

                                </td>


                                <td>

                                    <span
                                        class="student-status-badge status-<?= e($statusClass) ?>"
                                    >

                                        <i></i>

                                        <?= e($request['status']) ?>

                                    </span>

                                </td>


                                <td>

                                    <span class="student-date">

                                        <?= date(
                                            'M d, Y',
                                            strtotime($request['created_at'])
                                        ) ?>

                                    </span>

                                </td>

                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            </div>

        <?php endif; ?>

    </section>


    <!-- Information Card -->
    <div class="student-information">

        <div class="student-information-icon">
            !
        </div>

        <div>

            <strong>
                Before submitting a request
            </strong>

            <p>
                Provide complete and accurate information about your concern.
                This helps the support team process your request efficiently.
            </p>

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
                    <label for="dashboard-request-type">
                        Request type
                        <select id="dashboard-request-type" name="type_id" required>
                            <?php foreach ($types as $type): ?>
                                <option value="<?= (int) $type['id'] ?>"><?= e($type['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>

                <div class="request-form-field">
                    <label for="dashboard-request-subject">
                        Subject
                        <input id="dashboard-request-subject" name="subject" maxlength="180" required>
                    </label>
                </div>

                <div class="request-form-field request-form-field-full">
                    <label for="dashboard-request-description">
                        Description
                        <textarea id="dashboard-request-description" name="description" rows="7" required></textarea>
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