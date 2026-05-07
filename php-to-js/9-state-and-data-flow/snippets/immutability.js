// Never mutate state directly — always return new objects

const state = { count: 0, user: { name: 'Alice', role: 'admin' } };

// BAD: mutates
// state.count++;
// state.user.name = 'Bob';

// GOOD: spread for shallow copy
const next = { ...state, count: state.count + 1 };
console.log('original count:', state.count); // 0
console.log('next count:', next.count);       // 1

// Nested update — spread each level
const withNewName = {
  ...state,
  user: { ...state.user, name: 'Bob' },
};
console.log('original user:', state.user.name);  // Alice
console.log('new user:', withNewName.user.name); // Bob

// Array: filter/map are non-mutating; toSpliced/toSorted are ES2023 alternatives
const items = ['a', 'b', 'c'];
const without = items.filter(x => x !== 'b');
console.log('original:', items);    // ['a', 'b', 'c']
console.log('without b:', without); // ['a', 'c']
