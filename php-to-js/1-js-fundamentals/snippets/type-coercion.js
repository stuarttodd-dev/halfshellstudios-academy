// JavaScript type coercion with == (loose equality)
//
// PHP 8 tightened its comparison rules (0 == "foo" is now false in PHP 8).
// JavaScript == still performs implicit coercion, which produces surprising
// results. Always use === in JS to avoid this.

// Numeric string coercion: '1' converts to 1, so this is true.
console.log(0 == '0');           // true  — '0' converts to 0
console.log(0 === '0');          // false — strict, no coercion

// Non-numeric strings convert to NaN, so 0 == 'foo' is actually false in JS too.
console.log(0 == 'foo');         // false — 'foo' → NaN, and 0 != NaN

console.log(null == undefined);  // true  — special rule: they are == to each other
console.log(null === undefined); // false — different types

console.log(typeof null);        // 'object' — historical bug, never fixed

// The classic loose-equality table gotchas (all true with ==):
console.log('' == false);        // true  — both coerce to 0
console.log('0' == false);       // true  — '0' → 0, false → 0
console.log(0 == false);         // true  — false → 0
console.log('' == 0);            // true  — '' → 0

// PHP 8 comparison for reference:
// var_dump(0 == "0")    → true   (numeric string comparison)
// var_dump('' == false) → true
// var_dump('' == 0)     → false  (PHP 8 fixed this — no longer coerces '' to 0)
// In JS:  '' == 0      → true   (still coerces in JS)
//
// Rule: always use === in JS, just as you'd use strict comparison in PHP.

console.log('\n--- strict equality avoids all of the above ---');
console.log(0 === '');    // false
console.log(0 === false); // false
console.log(0 === '0');   // false
