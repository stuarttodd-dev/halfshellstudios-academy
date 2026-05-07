// ES6 class syntax is syntactic sugar over JavaScript's prototype system.
// Under the hood, methods live on the prototype object and are shared across
// all instances — there is no per-instance copy of each method.
//
// PHP comparison: PHP classes are compiled to vtables. JS exposes the chain
// directly and lets you walk it at runtime.

class Animal {
  constructor(name) { this.name = name; }
  speak() { return `${this.name} makes a sound.`; }
}

class Dog extends Animal {
  // Overrides the parent method
  speak() { return `${this.name} barks.`; }

  // super.speak() calls the parent's method — same as parent::speak() in PHP
  speakPolitely() { return super.speak() + ' (politely)'; }
}

const d = new Dog('Rex');
console.log(d.speak());          // Rex barks.
console.log(d.speakPolitely());  // Rex makes a sound. (politely)

// ---------------------------------------------------------------------------
// Walk the prototype chain manually
// ---------------------------------------------------------------------------

// d → Dog.prototype → Animal.prototype → Object.prototype → null

console.log(Object.getPrototypeOf(d) === Dog.prototype);                  // true
console.log(Object.getPrototypeOf(Dog.prototype) === Animal.prototype);   // true
console.log(Object.getPrototypeOf(Animal.prototype) === Object.prototype); // true
console.log(Object.getPrototypeOf(Object.prototype));                     // null

// instanceof checks the chain — same semantics as PHP's instanceof
console.log(d instanceof Dog);    // true
console.log(d instanceof Animal); // true  — Dog extends Animal
console.log(d instanceof Object); // true  — everything extends Object

// ---------------------------------------------------------------------------
// Own properties vs prototype properties
// ---------------------------------------------------------------------------

// `name` is an own property (set in the constructor via this.name)
// `speak` lives on Dog.prototype, not on d itself
console.log(d.hasOwnProperty('name'));  // true
console.log(d.hasOwnProperty('speak')); // false

// You can see what's on the prototype directly:
console.log(typeof Dog.prototype.speak); // 'function'

// ---------------------------------------------------------------------------
// Object.create: the primitive behind class inheritance
// ---------------------------------------------------------------------------

const proto = { greet() { return `Hi, I'm ${this.name}`; } };
const obj   = Object.create(proto);
obj.name    = 'Fido';
console.log(obj.greet());                          // Hi, I'm Fido
console.log(Object.getPrototypeOf(obj) === proto); // true
