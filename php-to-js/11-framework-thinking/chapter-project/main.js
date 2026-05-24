import { visibleItems } from './visible-items.js';

let state = {
  activeTab: 'overview',
  filterStatus: 'all',
  items: [
    { id: 1, name: 'Widget', status: 'active' },
    { id: 2, name: 'Gadget', status: 'pending' },
  ],
  count: 0,
};

function render() {
  console.log(`tab:${state.activeTab} filter:${state.filterStatus} count:${state.count}`);
  visibleItems(state).forEach((item) => console.log(` - ${item.name}`));
}

function dispatch(action) {
  if (action.type === 'SET_TAB') state = { ...state, activeTab: action.tab };
  if (action.type === 'SET_FILTER') state = { ...state, filterStatus: action.status };
  if (action.type === 'INCREMENT') state = { ...state, count: state.count + 1 };
  render();
}

render();
dispatch({ type: 'SET_TAB', tab: 'details' });
dispatch({ type: 'SET_FILTER', status: 'active' });
dispatch({ type: 'INCREMENT' });
dispatch({ type: 'INCREMENT' });
