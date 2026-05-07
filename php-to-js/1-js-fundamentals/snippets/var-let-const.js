// var: function-scoped and hoisted to the top of the function.
// The declaration is moved up but the assignment stays in place,
// so reading before the assignment yields undefined rather than a ReferenceError.
function varDemo() {
  console.log(x); // undefined (hoisted, not ReferenceError)
  var x = 1;
  console.log(x); // 1
}
varDemo();

// let: block-scoped. Exists in a Temporal Dead Zone (TDZ) from the start
// of the block until the declaration is reached. Reading in the TDZ throws.
{
  // console.log(y); // ReferenceError: Cannot access 'y' before initialization
  let y = 2;
  console.log(y); // 2
}

// const: the binding cannot be reassigned, but the object it points to
// can still be mutated. This is equivalent to a final reference in Java,
// or a variable typed to a class in PHP where the object can change state.
const obj = { n: 1 };
obj.n = 2;          // fine — mutating the object, not the binding
console.log(obj.n); // 2
// obj = {};        // TypeError: Assignment to constant variable

// PHP comparison:
// PHP has no hoisting. Variables simply do not exist until assigned.
// JS var hoisting is a common source of bugs — prefer let/const.
