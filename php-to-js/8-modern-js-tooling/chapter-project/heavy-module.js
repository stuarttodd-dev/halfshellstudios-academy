// Simulates a "heavy" module loaded on demand
export default {
  run() {
    console.log('Heavy module loaded lazily via dynamic import()');
  }
};
