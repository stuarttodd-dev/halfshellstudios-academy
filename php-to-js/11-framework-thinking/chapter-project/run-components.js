import { createApp } from './create-app.js';

const app = createApp({
  activeTab: 'overview',
  filterStatus: 'all',
  items: [
    { id: 1, name: 'Widget', status: 'active' },
    { id: 2, name: 'Gadget', status: 'pending' },
  ],
  count: 0,
});

app.render();
app.dispatch({ type: 'SET_TAB', tab: 'details' });
app.list.setFilter('active');
app.counter.increment();
app.counter.increment();
