import { visibleItems } from './visible-items.js';
import { createCounter } from './create-counter.js';
import { createTabs } from './create-tabs.js';
import { createFilterableList } from './create-filterable-list.js';

export function createApp(initialState) {
  let state = { ...initialState };

  const counter = createCounter({
    getCount: () => state.count,
    onIncrement: () => dispatch({ type: 'INCREMENT' }),
    onDecrement: () => dispatch({ type: 'DECREMENT' }),
  });

  const tabs = createTabs({
    tabs: ['overview', 'details'],
    getActive: () => state.activeTab,
    onSelect: (tab) => dispatch({ type: 'SET_TAB', tab }),
  });

  const list = createFilterableList({
    getState: () => state,
    onFilter: (status) => dispatch({ type: 'SET_FILTER', status }),
  });

  function renderApp() {
    console.log(`tab:${state.activeTab} filter:${state.filterStatus} count:${state.count}`);
    visibleItems(state).forEach((item) => console.log(` - ${item.name}`));
    console.log(`ui: ${tabs.render()} | ${counter.render()} | list:[${list.render().join(', ')}]`);
  }

  function dispatch(action) {
    if (action.type === 'SET_TAB') state = { ...state, activeTab: action.tab };
    if (action.type === 'SET_FILTER') state = { ...state, filterStatus: action.status };
    if (action.type === 'INCREMENT') state = { ...state, count: state.count + 1 };
    if (action.type === 'DECREMENT') state = { ...state, count: state.count - 1 };
    renderApp();
  }

  return {
    dispatch,
    getState: () => state,
    counter,
    tabs,
    list,
    render: renderApp,
    destroy() {
      counter.destroy();
      tabs.destroy();
      list.destroy();
    },
  };
}
