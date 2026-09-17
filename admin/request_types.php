<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/auth.php';

requireRole('admin');

/*
|--------------------------------------------------------------------------
| Add Request Type
|--------------------------------------------------------------------------
*/
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    trim($_POST['name'] ?? '') !== ''
) {
    $statement = $pdo->prepare(
        'INSERT INTO request_types (name, description) VALUES (?, ?)'
    );

    $statement->execute([
        trim($_POST['name']),
        trim($_POST['description'] ?? '')
    ]);

    header('Location: request_types.php');
    exit;
}

/*
|--------------------------------------------------------------------------
| Get Request Types
|--------------------------------------------------------------------------
*/
$types = $pdo->query(
    'SELECT * FROM request_types ORDER BY name ASC'
)->fetchAll();

$pageTitle = 'Request Types';
$basePath = '../';

require __DIR__ . '/../includes/header.php';
?>

<div class="request-types-page">

    <!-- =====================================================
         PAGE HEADING
         ===================================================== -->

<div class="page-heading request-types-heading">

    <div>
        <h1>Request Types</h1>

        <p class="muted">
            Manage the categories students can select when submitting a request.
        </p>
    </div>

    <div class="request-types-actions">

        <button
            class="button add-button"
            type="button"
            id="toggle-add-type"
        >
            + Add Type
        </button>

        <a
            class="button secondary button-compact"
            href="dashboard.php"
        >
            Back to Dashboard
        </a>

    </div>

</div>


    <!-- =====================================================
         ADD REQUEST TYPE FORM
         ===================================================== -->

    <form
        id="add-type-form"
        class="add-form is-hidden"
        method="post"
    >

        <div>
            <label for="type-name">
                Type Name
            </label>

            <input
                id="type-name"
                name="name"
                type="text"
                placeholder="e.g. Scholarship"
                maxlength="100"
                autocomplete="off"
                required
            >
        </div>


        <div>
            <label for="type-description">
                Description
            </label>

            <input
                id="type-description"
                name="description"
                type="text"
                placeholder="Short description"
                maxlength="255"
            >
        </div>


        <button
            class="button"
            type="submit"
        >
            Save Type
        </button>

        <button
            class="button secondary"
            type="button"
            id="cancel-add-type"
        >
            Cancel
        </button>

    </form>


    <!-- =====================================================
         REQUEST TYPES TABLE
         ===================================================== -->

    <div class="table-wrap">

        <table>

            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                </tr>
            </thead>

            <tbody>

            <?php if (!$types): ?>

                <tr>
                    <td
                        colspan="2"
                        class="request-types-empty"
                    >
                        <div class="request-types-empty-icon">
                            +
                        </div>

                        <strong>No request types yet</strong>

                        <span>
                            Click "+ Add Type" above to create your first request category.
                        </span>
                    </td>
                </tr>

            <?php else: ?>

                <?php foreach ($types as $type): ?>

                    <tr>

                        <td>
                            <span class="type-name">
                                <?= e($type['name']) ?>
                            </span>
                        </td>

                        <td>
                            <?=
                                e(
                                    trim($type['description'] ?? '') !== ''
                                        ? $type['description']
                                        : 'No description provided.'
                                )
                            ?>
                        </td>

                    </tr>

                <?php endforeach; ?>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const toggleButton = document.getElementById('toggle-add-type');
    const cancelButton = document.getElementById('cancel-add-type');
    const form = document.getElementById('add-type-form');
    const nameInput = document.getElementById('type-name');
    const descriptionInput = document.getElementById('type-description');

    if (!toggleButton || !cancelButton || !form || !nameInput || !descriptionInput) {
        return;
    }

    toggleButton.addEventListener('click', function () {

        const isHidden = form.classList.toggle('is-hidden');

        if (!isHidden) {
            nameInput.focus();
        }

    });

    cancelButton.addEventListener('click', function () {
        form.classList.add('is-hidden');
        nameInput.value = '';
        descriptionInput.value = '';
    });

});
</script>


<?php require __DIR__ . '/../includes/footer.php'; ?>