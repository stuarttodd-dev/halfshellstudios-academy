// `this` in JavaScript is determined by how a function is called, not where
// it is defined (except for arrow functions, which have no own `this`).
//
// PHP comparison: $this always refers to the current object instance.
// In JS, `this` can be anything depending on call context — a common trap.

// ---------------------------------------------------------------------------
// Rule 1: Default binding
// `this` is globalThis in sloppy mode, undefined in strict mode.
// ---------------------------------------------------------------------------
function whoAmI() {
  // In Node (non-strict) this is the global object; in strict mode: undefined.
  return typeof this;
}
console.log('Rule 1 — default binding:', whoAmI()); // 'object' (global) or 'undefined'

// ---------------------------------------------------------------------------
// Rule 2: Implicit binding
// The object to the left of the dot at the call site sets `this`.
// ---------------------------------------------------------------------------
const user = {
  name: 'Alice',
  greet() { return `Hi, ${this.name}`; },
};
console.log('Rule 2 — implicit:', user.greet()); // 'Hi, Alice'

// Detaching the method loses the implicit binding:
const detached = user.greet;
console.log('Rule 2 — detached:', detached()); // 'Hi, undefined' (or error in strict)

// ---------------------------------------------------------------------------
// Rule 3: Explicit binding — .call(), .apply(), .bind()
// ---------------------------------------------------------------------------
function greet(greeting) { return `${greeting}, ${this.name}`; }

console.log('Rule 3 — .call():', greet.call({ name: 'Bob' }, 'Hello'));   // 'Hello, Bob'
console.log('Rule 3 — .apply():', greet.apply({ name: 'Eve' }, ['Hi']));  // 'Hi, Eve'

const boundGreet = greet.bind({ name: 'Carol' });
console.log('Rule 3 — .bind():', boundGreet('Hey')); // 'Hey, Carol'

// ---------------------------------------------------------------------------
// Rule 4: new binding — constructor call
// `new` creates a fresh object and sets `this` to it inside the function.
// ---------------------------------------------------------------------------
function Person(name) { this.name = name; }
const p = new Person('Dave');
console.log('Rule 4 — new:', p.name); // 'Dave'

// ---------------------------------------------------------------------------
// Arrow functions: no own `this`
// They inherit `this` from the surrounding lexical scope at definition time.
// ---------------------------------------------------------------------------
class Timer {
  constructor() { this.seconds = 0; }

  start() {
    // Arrow function: `this` is the Timer instance, inherited from `start`.
    const tick = () => this.seconds++;
    tick();
    tick();
    // A regular function here would need .bind(this) to work correctly.
  }
}

const t = new Timer();
t.start();
console.log('Arrow this:', t.seconds); // 2
