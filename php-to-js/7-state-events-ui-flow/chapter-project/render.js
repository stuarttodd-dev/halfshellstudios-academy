import { openCount, visibleTasks } from './store.js';

export function render(ui, state) {
  ui.openCount.textContent = String(openCount(state));

  for (const button of ui.filterButtons) {
    const filter = button.dataset.filter;
    button.classList.toggle('is-active', filter === state.filter);
    button.setAttribute('aria-pressed', String(filter === state.filter));
  }

  ui.list.replaceChildren();
  for (const task of visibleTasks(state)) {
    const item = document.createElement('li');
    item.dataset.id = String(task.id);

    const toggle = document.createElement('button');
    toggle.type = 'button';
    toggle.className = 'toggle';
    toggle.textContent = task.done ? 'Undo' : 'Done';
    toggle.dataset.action = 'toggle';

    const title = document.createElement('span');
    title.className = 'title';
    title.textContent = task.title;
    if (task.done) title.classList.add('is-done');

    item.append(toggle, title);
    ui.list.append(item);
  }
}
