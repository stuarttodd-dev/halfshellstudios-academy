// Demonstrates: sync runs first, Promise microtasks next, setTimeout last
//
// The event loop processes the call stack completely before draining the
// microtask queue. Only after the microtask queue is empty does it pick
// the next macrotask (setTimeout, setInterval, I/O callbacks, etc.).

console.log('1 — sync');

setTimeout(() => console.log('4 — setTimeout (macrotask)'), 0);

Promise.resolve().then(() => console.log('2 — Promise .then (microtask)'));

queueMicrotask(() => console.log('3 — queueMicrotask'));

console.log('1b — still sync');

// Expected output order:
// 1 — sync
// 1b — still sync
// 2 — Promise .then (microtask)
// 3 — queueMicrotask
// 4 — setTimeout (macrotask)
//
// PHP comparison: PHP has no event loop. Each request runs synchronously
// top-to-bottom. In JS a single thread juggles many tasks via this queue.
