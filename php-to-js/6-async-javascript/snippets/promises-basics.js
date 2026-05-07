// A Promise is a placeholder for a future value.
// States: pending → fulfilled | rejected  (one-way; settled = final)

const delay = (ms) => new Promise(resolve => setTimeout(resolve, ms));
const failing = () => new Promise((_, reject) => reject(new Error('Network error')));

// .then / .catch / .finally
delay(100)
  .then(() => console.log('resolved after 100ms'))
  .catch(e => console.error('never reached'))
  .finally(() => console.log('always runs'));

failing()
  .then(() => console.log('never reached'))
  .catch(e => console.error('caught:', e.message))
  .finally(() => console.log('finally after rejection'));

// async/await is sugar over .then/.catch
async function run() {
  await delay(50);
  console.log('async/await version');
  try {
    await failing();
  } catch (e) {
    console.error('try/catch caught:', e.message);
  }
}
run();
