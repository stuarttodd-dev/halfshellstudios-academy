import {
  validateLabel,
  renderList,
  loadRows,
  saveRows,
  loadDraft,
  saveDraft,
  showFieldError,
} from './app.js';

const form = document.querySelector('[data-role="task-form"]');
const labelInput = document.querySelector('[data-role="label-input"]');
const labelError = document.querySelector('[data-role="label-error"]');
const listEl = document.querySelector('[data-role="result-list"]');

let rows = loadRows();

labelInput.value = loadDraft();
renderList(listEl, rows);

labelInput.addEventListener('input', () => {
  saveDraft(labelInput.value);
  showFieldError(labelInput, labelError, validateLabel(labelInput.value));
});

form.addEventListener('submit', (event) => {
  event.preventDefault();

  const message = validateLabel(labelInput.value);
  showFieldError(labelInput, labelError, message);
  if (message) {
    labelInput.focus();
    return;
  }

  rows = [...rows, { id: Date.now(), label: labelInput.value.trim() }];
  saveRows(rows);
  renderList(listEl, rows);

  labelInput.value = '';
  saveDraft('');
  showFieldError(labelInput, labelError, '');
  labelInput.focus();
});

listEl.addEventListener('click', (event) => {
  const li = event.target.closest('li');
  if (!li) return;

  const id = Number(li.dataset.id);
  rows = rows.filter((row) => row.id !== id);
  saveRows(rows);
  renderList(listEl, rows);
});
