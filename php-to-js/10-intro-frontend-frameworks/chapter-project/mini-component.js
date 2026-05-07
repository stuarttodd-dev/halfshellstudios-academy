// A tiny component system — this is exactly what Vue/React formalise
// Run: node mini-component.js

function createComponent({ state: initialState, render, events = {} }) {
  let state = { ...initialState };
  const subscribers = [];

  function setState(patch) {
    state = { ...state, ...(typeof patch === 'function' ? patch(state) : patch) };
    subscribers.forEach(fn => fn(state));
  }

  function subscribe(fn) {
    subscribers.push(fn);
    return () => subscribers.splice(subscribers.indexOf(fn), 1);
  }

  // Wire events — each returns a function that updates state
  const actions = Object.fromEntries(
    Object.entries(events).map(([name, fn]) => [name, (...args) => setState(fn(state, ...args))])
  );

  return {
    getState: () => state,
    setState,
    subscribe,
    actions,
    render: () => render(state, actions),
  };
}

// Counter component
const counter = createComponent({
  state: { count: 0, step: 1 },
  render: ({ count, step }) => `Counter: ${count} (step: ${step})`,
  events: {
    increment: (s)       => ({ count: s.count + s.step }),
    decrement: (s)       => ({ count: s.count - s.step }),
    setStep:   (s, step) => ({ step }),
  },
});

// Subscribe → re-render on change
counter.subscribe(() => console.log('→', counter.render()));

console.log('Initial:', counter.render());
counter.actions.increment();
counter.actions.increment();
counter.actions.setStep(5);
counter.actions.increment();
counter.actions.decrement();
