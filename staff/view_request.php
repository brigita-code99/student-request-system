<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('staff');

/*
|--------------------------------------------------------------------------
| Get Request ID
|--------------------------------------------------------------------------
*/

$requestId = (int) ($_GET['id'] ?? 0);

if ($requestId <= 0) {
    http_response_code(400);
    exit('Invalid request ID.');
}


/*
|--------------------------------------------------------------------------
| Fetch Request
|--------------------------------------------------------------------------
*/

$statement = $pdo->prepare("
    SELECT
        r.*,
        u.name AS student_name,
        u.email AS student_email,
        t.name AS type_name
    FROM requests r

    INNER JOIN users u
        ON u.id = r.student_id

    INNER JOIN request_types t
        ON t.id = r.type_id

    WHERE r.id = ?

    LIMIT 1
");

$statement->execute([$requestId]);

$request = $statement->fetch(PDO::FETCH_ASSOC);

if (!$request) {
    http_response_code(404);
    exit('Request not found.');
}


/*
|--------------------------------------------------------------------------
| Format Date
|--------------------------------------------------------------------------
*/

$createdDate = !empty($request['created_at'])
    ? date('M d, Y', strtotime($request['created_at']))
    : '—';

$createdTime = !empty($request['created_at'])
    ? date('h:i A', strtotime($request['created_at']))
    : '';


/*
|--------------------------------------------------------------------------
| Status Class
|--------------------------------------------------------------------------
*/

$status = trim((string) $request['status']);

$statusClass = strtolower(
    preg_replace(
        '/[^a-z0-9]+/i',
        '-',
        $status
    )
);


/*
|--------------------------------------------------------------------------
| Popup Content
|--------------------------------------------------------------------------
*/

?>

<div class="request-modal-content">

    <!-- =====================================================
         MODAL HEADER
         ===================================================== -->

    <div class="request-modal-header">

        <div class="request-modal-heading">

            <span class="request-modal-label">
                Request #<?= (int) $request['id'] ?>
            </span>

            <h2>
                <?= e($request['subject']) ?>
            </h2>

        </div>

        <button
            type="button"
            class="request-modal-close"
            id="close-request-modal"
            aria-label="Close request details"
        >
            ×
        </button>

    </div>


    <!-- =====================================================
         REQUEST INFORMATION
         ===================================================== -->

    <div class="request-modal-info-grid">

        <div class="request-info-item">

            <span class="request-info-label">
                Student
            </span>

            <strong>
                <?= e($request['student_name']) ?>
            </strong>

            <?php if (!empty($request['student_email'])): ?>

                <small>
                    <?= e($request['student_email']) ?>
                </small>

            <?php endif; ?>

        </div>


        <div class="request-info-item">

            <span class="request-info-label">
                Request Type
            </span>

            <strong>
                <?= e($request['type_name']) ?>
            </strong>

        </div>


        <div class="request-info-item">

            <span class="request-info-label">
                Submitted
            </span>

            <strong>
                <?= e($createdDate) ?>
            </strong>

            <?php if ($createdTime): ?>

                <small>
                    <?= e($createdTime) ?>
                </small>

            <?php endif; ?>

        </div>


        <div class="request-info-item">

            <span class="request-info-label">
                Current Status
            </span>

            <span
                class="staff-request-status status-<?= e($statusClass) ?>"
            >
                <span class="staff-status-dot"></span>

                <?= e($status) ?>
            </span>

        </div>

    </div>


    <!-- =====================================================
         REQUEST DESCRIPTION
         ===================================================== -->

    <div class="request-modal-section">

        <h3>
            Request Description
        </h3>

        <div class="request-description-box">

            <?php if (!empty(trim($request['description'] ?? ''))): ?>

                <?= nl2br(e($request['description'])) ?>

            <?php else: ?>

                <span class="request-no-content">
                    No description was provided.
                </span>

            <?php endif; ?>

        </div>

    </div>


    <!-- =====================================================
         UPDATE REQUEST
         ===================================================== -->

    <form
        class="request-update-form"
        method="post"
        action="update_request.php"
        id="request-update-form"
    >

        <input
            type="hidden"
            name="id"
            value="<?= (int) $request['id'] ?>"
        >


        <div class="request-modal-section">

            <h3>
                Update Request
            </h3>


            <div class="request-form-grid">

                <div class="request-form-field">

                    <label for="request-status">
                        Status
                    </label>

                    <select
                        id="request-status"
                        name="status"
                        required
                    >

                        <?php foreach (
                            ['Pending', 'In Progress', 'Resolved', 'Rejected']
                            as $availableStatus
                        ): ?>

                            <option
                                value="<?= e($availableStatus) ?>"
                                <?= $status === $availableStatus ? 'selected' : '' ?>
                            >
                                <?= e($availableStatus) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>

            </div>


            <div class="request-form-field">

                <label for="staff-notes">
                    Staff Notes
                </label>

                <textarea
                    id="staff-notes"
                    name="staff_notes"
                    rows="5"
                    placeholder="Add notes about this request..."
                ><?= e($request['staff_notes'] ?? '') ?></textarea>

            </div>

        </div>


        <!-- =================================================
             FORM ACTIONS
             ================================================= -->

        <div class="request-modal-footer">

            <button
                type="button"
                class="button secondary"
                id="cancel-request-modal"
            >
                Close
            </button>

            <button
                type="submit"
                class="button primary"
            >
                Save Update
            </button>

        </div>

    </form>

</div>