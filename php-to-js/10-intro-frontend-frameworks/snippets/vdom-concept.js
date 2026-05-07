// Virtual DOM: just a JavaScript object describing the UI
// React/Vue do this internally; we do it manually here to see the concept

function h(tag, props = {}, ...children) {
  return { tag, props, children: children.flat() };
}

function diff(oldNode, newNode) {
  if (!oldNode) return { type: 'CREATE', node: newNode };
  if (!newNode) return { type: 'REMOVE' };
  if (oldNode.tag !== newNode.tag) return { type: 'REPLACE', node: newNode };
  const propChanges = Object.entries(newNode.props).filter(([k, v]) => oldNode.props[k] !== v);
  return { type: 'UPDATE', props: propChanges };
}

const v1 = h('div', { class: 'card', id: 'main' },
  h('h1', {}, 'Hello'),
  h('p',  { class: 'text' }, 'World'),
);

const v2 = h('div', { class: 'card card--active', id: 'main' },
  h('h1', {}, 'Hello'),
  h('p',  { class: 'text' }, 'Updated'),
);

console.log('VNode:', JSON.stringify(v1, null, 2));
console.log('\nDiff root:', diff(v1, v2));
console.log('Diff h1:',   diff(v1.children[0], v2.children[0]));
console.log('Diff p:',    diff(v1.children[1], v2.children[1]));
