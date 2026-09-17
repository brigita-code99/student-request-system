<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('admin');

$requests = $pdo->query("
    SELECT
        r.*,
        u.name AS student_name,
        u.email,
        t.name AS type_name
    FROM requests r
    JOIN users u ON u.id = r.student_id
    JOIN request_types t ON t.id = r.type_id
    ORDER BY r.created_at DESC
")->fetchAll();

$pageTitle = 'All Requests';
$basePath = '../';

require __DIR__ . '/../includes/header.php';
?>

<div class="requests-page">

    <!-- =====================================================
         PAGE HEADING
         ===================================================== -->

    <div class="page-heading requests-heading">

        <div>
            <h1>All Requests</h1>

            <p class="muted">
                Review submitted requests without leaving the dashboard.
            </p>
        </div>

        <a
            class="button secondary button-compact"
            href="dashboard.php"
        >
            Back to Dashboard
        </a>

    </div>


    <!-- =====================================================
         REQUESTS TABLE
         ===================================================== -->

    <div class="table-wrap requests-table-wrap">

        <table class="requests-table">

            <thead>
                <tr>
                    <th>Student</th>
                    <th>Subject</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Created</th>
                </tr>
            </thead>

            <tbody>

            <?php if (!$requests): ?>

                <tr>
                    <td
                        colspan="5"
                        class="requests-empty"
                    >
                        <div class="requests-empty-icon">
                            ✓
                        </div>

                        <strong>No requests found</strong>

                        <span>
                            There are currently no submitted student requests.
                        </span>
                    </td>
                </tr>

            <?php else: ?>

                <?php foreach ($requests as $request): ?>

                    <?php
                    $status = trim((string) $request['status']);

                    $statusClass = match (strtolower($status)) {
                        'pending'     => 'pending',
                        'in progress' => 'in-progress',
                        'resolved'    => 'resolved',
                        'rejected'    => 'rejected',
                        default       => 'default'
                    };
                    ?>

                    <tr>

                        <!-- Student -->

                        <td class="request-student">
                            <?= e($request['student_name']) ?>
                        </td>


                        <!-- Subject -->

                        <td class="request-subject">

                            <button
                                type="button"
                                class="subject-link"
                                data-subject="<?= e($request['subject']) ?>"
                                data-student="<?= e($request['student_name']) ?>"
                                data-email="<?= e($request['email']) ?>"
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


                        <!-- Type -->

                        <td>
                            <?= e($request['type_name']) ?>
                        </td>


                        <!-- Status -->

                        <td>

                            <span class="request-status <?= e($statusClass) ?>">
                                <?= e($status) ?>
                            </span>

                        </td>


                        <!-- Created -->

                        <td class="request-created">
                            <?= e($request['created_at']) ?>
                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>


<!-- =========================================================
     REQUEST DETAILS MODAL
     ========================================================= -->

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

                <span
                    class="request-modal-email"
                    data-modal-field="email"
                ></span>
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


        <div
            class="request-modal-description"
            data-modal-notes-container
            hidden
        >

            <b>Staff Notes</b>

            <p data-modal-field="staff-notes"></p>

        </div>

    </div>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const modal = document.getElementById('request-modal');

    if (!modal) {
        return;
    }

    const closeButton = modal.querySelector('.request-modal-close');

    const subjectButtons =
        document.querySelectorAll('.subject-link');

    const title =
        document.getElementById('request-modal-title');

    const fields = {

        student:
            modal.querySelector(
                '[data-modal-field="student"]'
            ),

        email:
            modal.querySelector(
                '[data-modal-field="email"]'
            ),

        type:
            modal.querySelector(
                '[data-modal-field="type"]'
            ),

        status:
            modal.querySelector(
                '[data-modal-field="status"]'
            ),

        created:
            modal.querySelector(
                '[data-modal-field="created"]'
            ),

        description:
            modal.querySelector(
                '[data-modal-field="description"]'
            ),

        staffNotes:
            modal.querySelector(
                '[data-modal-field="staff-notes"]'
            )
    };

    const notesContainer =
        modal.querySelector(
            '[data-modal-notes-container]'
        );

    let lastFocusedElement = null;


    /* -------------------------------------------------------
       OPEN MODAL
       ------------------------------------------------------- */

    function openModal(button) {

        lastFocusedElement = button;

        title.textContent =
            button.dataset.subject ||
            'Request Details';


        fields.student.textContent =
            button.dataset.student ||
            '—';


        fields.email.textContent =
            button.dataset.email ||
            '—';


        fields.type.textContent =
            button.dataset.type ||
            '—';


        fields.status.textContent =
            button.dataset.status ||
            '—';


        fields.created.textContent =
            button.dataset.created ||
            '—';


        fields.description.textContent =
            button.dataset.description ||
            'No description provided.';


        const notes =
            button.dataset.staffNotes ||
            '';


        if (notes.trim() !== '') {

            fields.staffNotes.textContent = notes;

            notesContainer.hidden = false;

        } else {

            fields.staffNotes.textContent = '';

            notesContainer.hidden = true;

        }


        modal.hidden = false;

        document.body.style.overflow = 'hidden';

        closeButton.focus();

    }


    /* -------------------------------------------------------
       CLOSE MODAL
       ------------------------------------------------------- */

    function closeModal() {

        modal.hidden = true;

        document.body.style.overflow = '';

        if (lastFocusedElement) {

            lastFocusedElement.focus();

        }

    }


    /* -------------------------------------------------------
       SUBJECT BUTTONS
       ------------------------------------------------------- */

    subjectButtons.forEach(function (button) {

        button.addEventListener('click', function () {

            openModal(button);

        });

    });


    /* -------------------------------------------------------
       CLOSE BUTTON
       ------------------------------------------------------- */

    closeButton.addEventListener(
        'click',
        closeModal
    );


    /* -------------------------------------------------------
       CLICK OUTSIDE MODAL
       ------------------------------------------------------- */

    modal.addEventListener('click', function (event) {

        if (event.target === modal) {

            closeModal();

        }

    });


    /* -------------------------------------------------------
       ESC KEY
       ------------------------------------------------------- */

    document.addEventListener('keydown', function (event) {

        if (
            event.key === 'Escape' &&
            !modal.hidden
        ) {

            closeModal();

        }

    });

});
</script>


<?php require __DIR__ . '/../includes/footer.php'; ?>