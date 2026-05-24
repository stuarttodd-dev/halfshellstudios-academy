export const initialState = {
  filter: 'all',
  tasks: [
    { id: 1, title: 'Write tests', done: false },
    { id: 2, title: 'Ship patch', done: true },
  ],
  nextId: 3,
};

export function visibleTasks(state) {
  if (state.filter === 'done') return state.tasks.filter((task) => task.done);
  if (state.filter === 'open') return state.tasks.filter((task) => !task.done);
  return state.tasks;
}

export function openCount(state) {
  return state.tasks.filter((task) => !task.done).length;
}

export function addTask(state, title) {
  const trimmed = title.trim();
  if (!trimmed) return state;
  return {
    ...state,
    tasks: [...state.tasks, { id: state.nextId, title: trimmed, done: false }],
    nextId: state.nextId + 1,
  };
}

export function toggleTask(state, id) {
  return {
    ...state,
    tasks: state.tasks.map((task) =>
      task.id === id ? { ...task, done: !task.done } : task,
    ),
  };
}

export function setFilter(state, next) {
  return { ...state, filter: next };
}

export function readFilterFromUrl() {
  const value = new URLSearchParams(window.location.search).get('filter');
  if (value === 'open' || value === 'done' || value === 'all') return value;
  return 'all';
}

export function writeFilterToUrl(filter) {
  const url = new URL(window.location.href);
  if (filter === 'all') {
    url.searchParams.delete('filter');
  } else {
    url.searchParams.set('filter', filter);
  }
  window.history.replaceState(null, '', url);
}
