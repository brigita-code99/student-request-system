document.querySelectorAll('.alert').forEach((alert) => {
    window.setTimeout(() => alert.remove(), 5000);
});

const requestModal = document.querySelector('#request-modal');

if (requestModal) {
    const modalTitle = requestModal.querySelector('#request-modal-title');
    const notesContainer = requestModal.querySelector('[data-modal-notes-container]');
    const closeModal = () => {
        requestModal.hidden = true;
    };

    document.querySelectorAll('.subject-link').forEach((subject) => {
        subject.addEventListener('click', () => {
            modalTitle.textContent = subject.dataset.subject || '';
            requestModal.querySelector('[data-modal-field="student"]').textContent = subject.dataset.student || '';
            requestModal.querySelector('[data-modal-field="email"]').textContent = subject.dataset.email || '';
            requestModal.querySelector('[data-modal-field="type"]').textContent = subject.dataset.type || '';
            requestModal.querySelector('[data-modal-field="status"]').textContent = subject.dataset.status || '';
            requestModal.querySelector('[data-modal-field="created"]').textContent = subject.dataset.created || '';
            requestModal.querySelector('[data-modal-field="description"]').textContent = subject.dataset.description || '';
            requestModal.querySelector('[data-modal-field="staff-notes"]').textContent = subject.dataset.staffNotes || '';
            notesContainer.hidden = !subject.dataset.staffNotes;
            requestModal.hidden = false;
            requestModal.querySelector('.request-modal-close').focus();
        });
    });

    requestModal.querySelector('.request-modal-close').addEventListener('click', closeModal);
    requestModal.addEventListener('click', (event) => {
        if (event.target === requestModal) {
            closeModal();
        }
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !requestModal.hidden) {
            closeModal();
        }
    });
}

const createRequestModal = document.querySelector('#create-request-modal');

if (createRequestModal) {
    const openButtons = document.querySelectorAll('.open-create-request');
    const closeButtons = document.querySelectorAll('.create-request-close');

    const openCreateModal = () => {
        createRequestModal.hidden = false;
        const firstField = createRequestModal.querySelector('input, select, textarea');
        if (firstField) {
            firstField.focus();
        }
    };

    const closeCreateModal = () => {
        createRequestModal.hidden = true;
    };

    openButtons.forEach((button) => {
        button.addEventListener('click', openCreateModal);
    });

    closeButtons.forEach((button) => {
        button.addEventListener('click', closeCreateModal);
    });

    createRequestModal.addEventListener('click', (event) => {
        if (event.target === createRequestModal) {
            closeCreateModal();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !createRequestModal.hidden) {
            closeCreateModal();
        }
    });
}
