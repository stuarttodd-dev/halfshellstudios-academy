export function createCounter({ getCount, onIncrement, onDecrement }) {
  return {
    increment: onIncrement,
    decrement: onDecrement,
    render() {
      return `counter:${getCount()}`;
    },
    destroy() {},
  };
}
