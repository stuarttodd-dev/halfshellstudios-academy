// State — single source of truth
const state = {
  activeTab: 'all',
  filter: '',
  counter: 0,
  items: [
    { id: 1, name: 'Bulbasaur',  type: 'grass'    },
    { id: 2, name: 'Charmander', type: 'fire'     },
    { id: 3, name: 'Squirtle',   type: 'water'    },
    { id: 4, name: 'Pikachu',    type: 'electric' },
    { id: 5, name: 'Gengar',     type: 'ghost'    },
    { id: 6, name: 'Snorlax',    type: 'normal'   },
  ],
};

// Derived state — never store what can be computed
function filteredItems() {
  return state.items
    .filter(i => state.activeTab === 'all' || i.type === state.activeTab)
    .filter(i => i.name.toLowerCase().includes(state.filter.toLowerCase()));
}

// Render — pure function of state
function render() {
  const filtered = filteredItems();
  console.clear?.();
  console.log(`\n=== PHP to JS — Pokédex (counter: ${state.counter}) ===`);
  console.log(`Tab: ${state.activeTab} | Filter: "${state.filter}" | Showing: ${filtered.length}`);
  filtered.forEach(i => console.log(`  • ${i.name} [${i.type}]`));
}

// Events — update state then re-render
function setTab(tab)    { state.activeTab = tab; render(); }
function setFilter(f)   { state.filter = f;      render(); }
function increment()    { state.counter++;        render(); }

// Demo
render();
setTab('fire');
render();
setFilter('s');
render();
increment();
increment();
render();
setTab('all');
setFilter('');
render();
