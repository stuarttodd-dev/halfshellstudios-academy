// fetch() + async/await — the browser's built-in HTTP client.
//
// Requires a browser environment (or Node 18+ which has global fetch).
// Paste into DevTools console, or run: node snippets/fetch-dom.js
//
// PHP comparison:
//   file_get_contents($url) / Guzzle::get($url)  →  await fetch(url)
//   json_decode($body, true)                      →  await res.json()
//   $response->getStatusCode()                    →  res.status

async function loadPokemon(name) {
  const res = await fetch(`https://pokeapi.co/api/v2/pokemon/${name}`);

  // fetch() does NOT throw on 4xx/5xx — you must check res.ok manually.
  // This is the most common gotcha for devs coming from curl or Guzzle.
  if (!res.ok) {
    throw new Error(`Request failed: ${res.status} ${res.statusText}`);
  }

  const data = await res.json();

  console.log(`Name:   ${data.name}`);
  console.log(`Weight: ${data.weight} hectograms`);
  console.log(`Height: ${data.height} decimetres`);

  // In a real page you would update the DOM here, e.g.:
  // document.getElementById('pokemon-name').textContent = data.name;

  return data;
}

// Top-level await works in ESM (.mjs) and DevTools.
// In a plain .js script wrap in an async IIFE:
(async () => {
  try {
    await loadPokemon('pikachu');
  } catch (err) {
    console.error('Failed to load Pokémon:', err.message);
  }
})();
