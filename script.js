// PROTOTYPE DATA ONLY!!

'use strict';
// JavaScript handles UI interactions.
// PHP handles task data and CRUD using MySQL


// 1. UI STATE
let filter = 'all';
let editingId = null;
let deletingForm = null;

// 2. PAGE REFERENCES
// Small helper to find an HTML element by its unique id attribute.
const getElement = (id) => document.getElementById(id);
const form = getElement('task-form');
const input = getElement('task-input');
const descriptionInput = getElement('task-description');
const list = getElement('task-list');
const dialog = getElement('delete-dialog');


// Keep the input's character counter in sync with what the user types.
function updateCount() {
  getElement('char-count').textContent = `${input.value.length}/120`;
}

// Return the shared add/edit form to its initial Add task state.
function resetForm() {
  editingId = null;
  form.reset();
  getElement('task-id').value = '';
  getElement('form-action').value = 'create';
  getElement('composer-title').textContent = 'What’s next?';
  getElement('submit-button').innerHTML = '<span aria-hidden="true">＋</span> Add task';
  getElement('cancel-edit').hidden = true;
  getElement('form-error').textContent = '';
  input.removeAttribute('aria-invalid');
  descriptionInput.removeAttribute('aria-invalid');
  updateCount();
}

// 4. FILTER THE PHP-RENDERED TASKS
function applyFilter() {

  const rows = list.querySelectorAll('.task-row');

  let visibleCount = 0;

  rows.forEach(row => {

    const completed = row.querySelector('.task-check').checked;

    const visible =
      filter === 'all' ||
      (filter === 'completed' && completed) ||
      (filter === 'active' && !completed);

    row.hidden = !visible;

    if (visible) {
      visibleCount++;
    }

  });

  getElement('empty-state').hidden = visibleCount > 0;

}

// 5. EDITING AND DELETE CONFIRMATION
// Fill the existing form instead of creating a second editing interface.
function editTask(id, text, description) {

    editingId = id;

    getElement('task-id').value = id;
    getElement('form-action').value = 'update';

    getElement('composer-title').textContent = 'A little adjustment.';
    input.value = text;
    descriptionInput.value = description;

    getElement('submit-button').textContent = 'Save changes';
    getElement('cancel-edit').hidden = false;

    getElement('form-error').textContent = '';
    input.removeAttribute('aria-invalid');

    updateCount();
    input.focus();
}

// 6. EVENT LISTENERS
// PHP handles form submissions and communicates with the MySQL database.
// EDIT TASK BUTTONS
document.querySelectorAll('[data-action="edit"]').forEach(button => {

    button.addEventListener('click', () => {

        const id = button.dataset.id;
        const text = button.dataset.text;
        const description = button.dataset.description;

        editTask(id, text, description);

    });

});

// FILTER BUTTONS
document.querySelectorAll('[data-filter]').forEach(button => {

    button.addEventListener('click', () => {

        filter = button.dataset.filter;

        document.querySelectorAll('[data-filter]').forEach(item => {

            item.setAttribute(
                'aria-pressed',
                String(item === button)
            );

        });

        applyFilter();

    });

});

// DELETE BUTTONS
document.querySelectorAll('.delete-form').forEach(deleteForm => {

    deleteForm.addEventListener('submit', event => {

        event.preventDefault();

        deletingForm = deleteForm;

        const row = deleteForm.closest('.task-row');
        const taskText = row.querySelector('.task-text').textContent;

        getElement('delete-description').textContent =
    '"' + taskText + '" will be removed from your list.';

        dialog.showModal();

    });

});

// CONFIRM DELETE
getElement('keep-task').addEventListener('click', () => {
    dialog.close();
});

getElement('confirm-delete').addEventListener('click', () => {

    if (deletingForm) {
        deletingForm.submit();
    }

});

// Clear the pending delete form when the dialog closes.
dialog.addEventListener('close', () => {
    deletingForm = null;
});

form.addEventListener('submit', event => {

    const text = input.value.trim();
    const description = descriptionInput.value.trim();

    if (!text || text.length > 120) {

        event.preventDefault();

        getElement('form-error').textContent =
            'Enter a task between 1 and 120 characters.';

        input.setAttribute('aria-invalid', 'true');
        input.focus();

        return;
    }

    if (!description) {

        event.preventDefault();

        getElement('form-error').textContent =
            'Please enter a task description.';

        descriptionInput.setAttribute('aria-invalid', 'true');
        descriptionInput.focus();

        return;
    }

});

// TASK INPUT LISTENER
input.addEventListener('input', () => {

    updateCount();

    input.removeAttribute('aria-invalid');

    getElement('form-error').textContent = '';

});

// TASK DESCRIPTION INPUT LISTENER
descriptionInput.addEventListener('input', () => {

    descriptionInput.removeAttribute('aria-invalid');
    getElement('form-error').textContent = '';

});


// CANCEL EDIT
getElement('cancel-edit').addEventListener('click', () => {

    resetForm();

    input.focus();

});



// 7. INITIAL DISPLAY
// PHP renders tasks from the MySQL database when the page loads..
updateCount();
applyFilter();
