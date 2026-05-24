export function validateLabel(value) {
  const t = value.trim();
  if (t === '') return 'Label is required.';
  if (t.length < 2) return 'Label is too short.';
  return '';
}

export function renderList(el, items) {
  el.replaceChildren();
  for (const row of items) {
    const li = document.createElement('li');
    li.textContent = row.label;
    li.dataset.id = String(row.id);
    el.append(li);
  }
}

const DRAFT_KEY = 'task-form-draft';
const ROWS_KEY = 'task-form-rows';

export function loadRows() {
  try {
    const raw = localStorage.getItem(ROWS_KEY);
    return raw ? JSON.parse(raw) : [];
  } catch {
    return [];
  }
}

export function saveRows(rows) {
  localStorage.setItem(ROWS_KEY, JSON.stringify(rows));
}

export function loadDraft() {
  return localStorage.getItem(DRAFT_KEY) ?? '';
}

export function saveDraft(value) {
  localStorage.setItem(DRAFT_KEY, value);
}

export function showFieldError(input, errorEl, message) {
  errorEl.textContent = message;
  input.classList.toggle('is-error', Boolean(message));
  input.classList.toggle('is-success', !message && input.value.trim() !== '');
  errorEl.classList.toggle('is-success', !message && input.value.trim() !== '');
}
