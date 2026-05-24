import {
  addTask,
  openCount,
  toggleTask,
  visibleTasks,
} from './store.js';

let state = {
  filter: 'all',
  tasks: [{ id: 1, title: 'A', done: false }],
  nextId: 2,
};

state = addTask(state, 'B');
state = toggleTask(state, 1);
state = { ...state, filter: 'open' };

console.log('open titles', visibleTasks(state).map((task) => task.title));
console.log('openCount', openCount(state));
