// Always handle Promise rejections — unhandled ones crash Node.js in newer versions.

// Pattern 1: try/catch with async/await
async function safe1() {
  try {
    const res = await fetch('https://pokeapi.co/api/v2/pokemon/pikachu');
    const data = await res.json();
    return data.name;
  } catch (e) {
    console.error('safe1 caught:', e.message);
    return null;
  }
}

// Pattern 2: .catch() on the promise chain
async function safe2() {
  return fetch('https://pokeapi.co/api/v2/pokemon/pikachu')
    .then(r => r.json())
    .then(d => d.name)
    .catch(e => { console.error('safe2 caught:', e.message); return null; });
}

// Pattern 3: explicit error result tuple (Result pattern)
async function safe3(url) {
  try {
    const data = await fetch(url).then(r => r.json());
    return [null, data];
  } catch (e) {
    return [e, null];
  }
}

(async () => {
  console.log(await safe1());
  console.log(await safe2());
  const [err, data] = await safe3('https://pokeapi.co/api/v2/pokemon/eevee');
  if (err) console.error(err.message);
  else console.log(data.name);
})();
