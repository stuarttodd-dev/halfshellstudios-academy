// A closure is a function that retains access to its enclosing scope even
// after that scope has finished executing.
//
// PHP has closures too (function() use ($x) {}), but JS closures capture
// variables by reference automatically — no `use` keyword needed.

function makeCounter(initial = 0) {
  let count = initial; // captured in the closure below

  return {
    increment: () => ++count,
    decrement: () => --count,
    value:     () => count,
    reset:     () => { count = initial; },
  };
}

const c = makeCounter(10);
console.log(c.increment()); // 11
console.log(c.increment()); // 12
console.log(c.decrement()); // 11
console.log(c.value());     // 11
c.reset();
console.log(c.value());     // 10

// ---------------------------------------------------------------------------
// Stale closure trap: var in a for loop
// ---------------------------------------------------------------------------
// var is function-scoped. All iterations share the SAME `i` binding.
// By the time any callback runs, the loop has finished and i === 3.

const fnsVar = [];
for (var i = 0; i < 3; i++) {
  fnsVar.push(() => i);
}
console.log('var loop (stale):', fnsVar.map(f => f())); // [3, 3, 3]

// Fix: let is block-scoped. Each iteration creates a fresh binding,
// so each closure captures a different copy of j.

const fnsLet = [];
for (let j = 0; j < 3; j++) {
  fnsLet.push(() => j);
}
console.log('let loop (correct):', fnsLet.map(f => f())); // [0, 1, 2]

// The same stale-closure problem appears in React useEffect and event
// handlers — always prefer let/const and be mindful of what each closure
// captures at the time it is created.
