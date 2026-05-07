class EventEmitter {
  #listeners = new Map();

  on(event, fn) {
    if (!this.#listeners.has(event)) this.#listeners.set(event, []);
    this.#listeners.get(event).push(fn);
    return () => this.off(event, fn); // returns unsubscribe
  }

  off(event, fn) {
    const list = this.#listeners.get(event) ?? [];
    this.#listeners.set(event, list.filter(l => l !== fn));
  }

  emit(event, payload) {
    (this.#listeners.get(event) ?? []).forEach(fn => fn(payload));
  }
}

// Usage — compare to Laravel Events/Listeners
const bus = new EventEmitter();
const unsub = bus.on('order:paid', ({ orderId, amount }) => {
  console.log(`Order ${orderId} paid: £${(amount / 100).toFixed(2)}`);
});

bus.emit('order:paid', { orderId: 42, amount: 2999 });
bus.emit('order:paid', { orderId: 43, amount: 1999 });

unsub(); // clean up
bus.emit('order:paid', { orderId: 44, amount: 4999 }); // not logged
