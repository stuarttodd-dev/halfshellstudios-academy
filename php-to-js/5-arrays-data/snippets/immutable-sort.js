const scores = [42, 7, 99, 15, 3];

// DANGER: sort() mutates the original
const dangerous = scores.sort((a, b) => a - b);
console.log('scores is now sorted (mutated):', scores); // [3, 7, 15, 42, 99]

// SAFE: copy first with spread or toSorted (ES2023)
const original = [42, 7, 99, 15, 3];
const sorted = [...original].sort((a, b) => a - b);
console.log('original intact:', original); // [42, 7, 99, 15, 3]
console.log('sorted copy:', sorted);       // [3, 7, 15, 42, 99]

// toSorted() — ES2023, non-mutating built-in
const sorted2 = original.toSorted((a, b) => a - b);
console.log('toSorted:', sorted2);         // [3, 7, 15, 42, 99]
console.log('original still:', original); // [42, 7, 99, 15, 3]
