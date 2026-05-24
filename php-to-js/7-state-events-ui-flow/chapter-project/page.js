import {
  initialState,
  addTask,
  toggleTask,
  setFilter,
  readFilterFromUrl,
  writeFilterToUrl,
} from './store.js';
import { render } from './render.js';

let state = {
  ...initialState,
  filter: readFilterFromUrl(),
};

const ui = {
  form: document.querySelector('[data-role="task-form"]'),
  titleInput: document.querySelector('[data-role="title-input"]'),
  list: document.querySelector('[data-role="task-list"]'),
  openCount: document.querySelector('[data-role="open-count"]'),
  filterButtons: [...document.querySelectorAll('[data-role="filter-tab"]')],
};

function update(nextState) {
  state = nextState;
  render(ui, state);
}

ui.form.addEventListener('submit', (event) => {
  event.preventDefault();
  update(addTask(state, ui.titleInput.value));
  ui.titleInput.value = '';
  ui.titleInput.focus();
});

for (const button of ui.filterButtons) {
  button.addEventListener('click', () => {
    const next = button.dataset.filter;
    writeFilterToUrl(next);
    update(setFilter(state, next));
  });
}

ui.list.addEventListener('click', (event) => {
  const button = event.target.closest('[data-action="toggle"]');
  if (!button) return;
  const item = button.closest('li');
  if (!item) return;
  update(toggleTask(state, Number(item.dataset.id)));
});

render(ui, state);
