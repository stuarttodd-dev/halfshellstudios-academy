import { greet } from './resources/js/lib/greet.js';

const pipeline = ['lint', 'format:check', 'test', 'build'];

console.log(greet('world'));

try {
  greet('');
} catch (error) {
  console.log('validation', error.message);
}

console.log('CI steps', pipeline.join(' → '));
