// Vue's ref() is conceptually this — a getter/setter pair that triggers subscribers
function ref(initialValue) {
  let _value = initialValue;
  const subscribers = [];
  return {
    get value() { return _value; },
    set value(v) {
      _value = v;
      subscribers.forEach(fn => fn(v));
    },
    watch(fn) { subscribers.push(fn); },
  };
}

const count = ref(0);
count.watch(v => console.log('count changed to:', v));

count.value++;   // logs: count changed to: 1
count.value = 5; // logs: count changed to: 5
console.log('current:', count.value); // 5
