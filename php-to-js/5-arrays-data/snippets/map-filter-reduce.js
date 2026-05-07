const nums = [1, 2, 3, 4, 5, 6, 7, 8, 9, 10];

// map — like PHP's array_map($callback, $array) but called as a method on the array
const doubled = nums.map(n => n * 2);
console.log('doubled:', doubled);

// filter — like PHP's array_filter($array, $callback)
const evens = nums.filter(n => n % 2 === 0);
console.log('evens:', evens);

// reduce — like PHP's array_reduce($array, $callback, $initial)
const sum = nums.reduce((acc, n) => acc + n, 0);
console.log('sum:', sum);

// Chain them — unlike PHP where you'd nest calls
const result = nums
  .filter(n => n % 2 === 0)    // [2, 4, 6, 8, 10]
  .map(n => n ** 2)             // [4, 16, 36, 64, 100]
  .reduce((s, n) => s + n, 0); // 220
console.log('sum of squares of evens:', result);

// find / some / every
console.log('first > 5:', nums.find(n => n > 5));  // 6
console.log('any > 9?',   nums.some(n => n > 9));  // true
console.log('all > 0?',   nums.every(n => n > 0)); // true
