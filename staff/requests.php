<?php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('staff');

/*
|--------------------------------------------------------------------------
| Fetch Student Requests
|--------------------------------------------------------------------------
*/

$stmt = $pdo->query("
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

    ORDER BY r.created_at DESC
");

$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);


/*
|--------------------------------------------------------------------------
| Page Setup
|--------------------------------------------------------------------------
*/

$pageTitle = 'Manage Requests';
$basePath = '../';

require __DIR__ . '/../includes/header.php';

?>

<div class="staff-requests-page">

    <!-- =====================================================
         PAGE HEADER
         ===================================================== -->

    <div class="page-heading staff-requests-heading">

        <div>

            <h1>Manage Requests</h1>

            <p class="muted">
                Review student submissions and update their request status.
            </p>

        </div>


        <div class="staff-requests-header-actions">

            <span class="staff-request-count">

                <strong>
                    <?= count($requests) ?>
                </strong>

                <?= count($requests) === 1 ? 'request' : 'requests' ?>

            </span>


            <a
                class="button secondary button-compact"
                href="dashboard.php"
            >
                Back to Dashboard
            </a>

        </div>

    </div>


    <!-- =====================================================
         REQUEST TABLE
         ===================================================== -->

    <?php if (!$requests): ?>

        <div class="staff-requests-empty">

            <div class="staff-requests-empty-icon">
                📭
            </div>

            <h2>No student requests</h2>

            <p>
                There are currently no requests available for review.
            </p>

            <a
                class="button secondary"
                href="dashboard.php"
            >
                Back to Dashboard
            </a>

        </div>

    <?php else: ?>

        <div class="staff-requests-table-wrap">

            <table class="staff-requests-table">

                <thead>

                    <tr>

                        <th class="request-student-column">
                            Student
                        </th>

                        <th>
                            Subject
                        </th>

                        <th>
                            Request Type
                        </th>

                        <th>
                            Status
                        </th>

                        <th>
                            Submitted
                        </th>

                        <th class="request-action-column">
                            Action
                        </th>

                    </tr>

                </thead>


                <tbody>

                    <?php foreach ($requests as $request): ?>

                        <?php

                        $status = trim((string) $request['status']);

                        $statusClass = strtolower(
                            preg_replace(
                                '/[^a-z0-9]+/i',
                                '-',
                                $status
                            )
                        );

                        $createdDate = !empty($request['created_at'])
                            ? date(
                                'M d, Y',
                                strtotime($request['created_at'])
                            )
                            : '—';

                        ?>

                        <tr>

                            <!-- Student -->

                            <td>

                                <div class="staff-request-student">

                                    <div class="staff-student-avatar">

                                        <?= e(
                                            strtoupper(
                                                substr(
                                                    trim(
                                                        $request['student_name']
                                                        ?? 'S'
                                                    ),
                                                    0,
                                                    1
                                                )
                                            )
                                        ) ?>

                                    </div>


                                    <div class="staff-student-details">

                                        <strong>
                                            <?= e(
                                                $request['student_name']
                                                ?? 'Unknown Student'
                                            ) ?>
                                        </strong>


                                        <?php if (!empty($request['student_email'])): ?>

                                            <small>
                                                <?= e(
                                                    $request['student_email']
                                                ) ?>
                                            </small>

                                        <?php endif; ?>

                                    </div>

                                </div>

                            </td>


                            <!-- Subject -->

                            <td>

                                <div class="staff-request-subject">

                                    <strong>
                                        <?= e(
                                            $request['subject']
                                            ?? 'Untitled Request'
                                        ) ?>
                                    </strong>


                                    <?php if (!empty($request['description'])): ?>

                                        <small>

                                            <?= e(
                                                mb_strimwidth(
                                                    trim(
                                                        $request['description']
                                                    ),
                                                    0,
                                                    75,
                                                    '...'
                                                )
                                            ) ?>

                                        </small>

                                    <?php endif; ?>

                                </div>

                            </td>


                            <!-- Request Type -->

                            <td>

                                <span class="staff-request-type">

                                    <?= e(
                                        $request['type_name']
                                        ?? '—'
                                    ) ?>

                                </span>

                            </td>


                            <!-- Status -->

                            <td>

                                <span
                                    class="staff-request-status status-<?= e($statusClass) ?>"
                                >

                                    <span class="staff-status-dot"></span>

                                    <?= e($status) ?>

                                </span>

                            </td>


                            <!-- Submitted -->

                            <td>

                                <div class="staff-request-date">

                                    <strong>
                                        <?= e($createdDate) ?>
                                    </strong>

                                </div>

                            </td>


                            <!-- Action -->

                            <td>

                                <button
                                    type="button"
                                    class="button primary button-compact staff-open-request"
                                    data-request-id="<?= (int) $request['id'] ?>"
                                >
                                    Open
                                </button>

                            </td>

                        </tr>

                    <?php endforeach; ?>

                </tbody>

            </table>

        </div>

    <?php endif; ?>

</div>


<!-- =========================================================
     REQUEST DETAILS MODAL
     ========================================================= -->

<div
    class="request-modal-overlay"
    id="request-modal"
    aria-hidden="true"
>

    <div
        class="request-modal-dialog"
        role="dialog"
        aria-modal="true"
        aria-labelledby="request-modal-title"
    >

        <div
            class="request-modal-loading"
            id="request-modal-loading"
        >

            <div class="request-modal-spinner"></div>

            <p>
                Loading request...
            </p>

        </div>


        <div
            class="request-modal-body"
            id="request-modal-body"
        ></div>

    </div>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const modal = document.getElementById('request-modal');
    const modalBody = document.getElementById('request-modal-body');
    const loading = document.getElementById('request-modal-loading');

    if (!modal || !modalBody || !loading) {
        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Open Modal
    |--------------------------------------------------------------------------
    */

    function openRequestModal(requestId) {

        if (!requestId) {
            return;
        }

        modal.classList.add('is-open');
        modal.setAttribute('aria-hidden', 'false');

        document.body.classList.add('modal-open');

        modalBody.innerHTML = '';
        loading.classList.add('is-visible');


        fetch(
            'view_request.php?id=' +
            encodeURIComponent(requestId),
            {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            }
        )
        .then(function (response) {

            if (!response.ok) {
                throw new Error(
                    'Unable to load request.'
                );
            }

            return response.text();

        })
        .then(function (html) {

            loading.classList.remove('is-visible');

            modalBody.innerHTML = html;

            bindModalEvents();

        })
        .catch(function (error) {

            loading.classList.remove('is-visible');

            modalBody.innerHTML = `
                <div class="request-modal-error">
                    <div class="request-modal-error-icon">!</div>
                    <h3>Unable to load request</h3>
                    <p>Please refresh the page and try again.</p>
                    <button
                        type="button"
                        class="button secondary"
                        id="error-close-modal"
                    >
                        Close
                    </button>
                </div>
            `;

            const errorClose =
                document.getElementById('error-close-modal');

            if (errorClose) {
                errorClose.addEventListener(
                    'click',
                    closeRequestModal
                );
            }

            console.error(error);
        });
    }


    /*
    |--------------------------------------------------------------------------
    | Close Modal
    |--------------------------------------------------------------------------
    */

    function closeRequestModal() {

        modal.classList.remove('is-open');

        modal.setAttribute(
            'aria-hidden',
            'true'
        );

        document.body.classList.remove('modal-open');

        setTimeout(function () {

            modalBody.innerHTML = '';

        }, 200);
    }


    /*
    |--------------------------------------------------------------------------
    | Bind Request Buttons
    |--------------------------------------------------------------------------
    */

    document
        .querySelectorAll('.staff-open-request')
        .forEach(function (button) {

            button.addEventListener(
                'click',
                function () {

                    const requestId =
                        this.dataset.requestId;

                    openRequestModal(requestId);

                }
            );

        });


    /*
    |--------------------------------------------------------------------------
    | Modal Events
    |--------------------------------------------------------------------------
    */

    function bindModalEvents() {

        const closeButton =
            document.getElementById(
                'close-request-modal'
            );

        const cancelButton =
            document.getElementById(
                'cancel-request-modal'
            );

        const updateForm =
            document.getElementById(
                'request-update-form'
            );


        if (closeButton) {

            closeButton.addEventListener(
                'click',
                closeRequestModal
            );

        }


        if (cancelButton) {

            cancelButton.addEventListener(
                'click',
                closeRequestModal
            );

        }


        /*
        |--------------------------------------------------------------------------
        | Update Request Without Leaving Page
        |--------------------------------------------------------------------------
        */

        if (updateForm) {

            updateForm.addEventListener(
                'submit',
                function (event) {

                    event.preventDefault();

                    const submitButton =
                        updateForm.querySelector(
                            'button[type="submit"]'
                        );

                    const originalText =
                        submitButton
                            ? submitButton.innerHTML
                            : 'Save Update';


                    if (submitButton) {

                        submitButton.disabled = true;

                        submitButton.innerHTML =
                            'Saving...';

                    }


                    const formData =
                        new FormData(updateForm);


                    fetch(
                        updateForm.action,
                        {
                            method: 'POST',
                            body: formData,
                            headers: {
                                'X-Requested-With':
                                    'XMLHttpRequest'
                            }
                        }
                    )
                    .then(function (response) {

                        return response.text();

                    })
                    .then(function (result) {

                        /*
                        | Reload the request list after saving.
                        */
                        window.location.reload();

                    })
                    .catch(function () {

                        if (submitButton) {

                            submitButton.disabled = false;

                            submitButton.innerHTML =
                                originalText;

                        }

                        alert(
                            'Unable to save the request. Please try again.'
                        );

                    });

                }
            );

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Close When Clicking Outside
    |--------------------------------------------------------------------------
    */

    modal.addEventListener(
        'click',
        function (event) {

            if (
                event.target === modal
            ) {
                closeRequestModal();
            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Close With ESC
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'keydown',
        function (event) {

            if (
                event.key === 'Escape' &&
                modal.classList.contains('is-open')
            ) {

                closeRequestModal();

            }

        }
    );

});
</script>


<?php require __DIR__ . '/../includes/footer.php'; ?>