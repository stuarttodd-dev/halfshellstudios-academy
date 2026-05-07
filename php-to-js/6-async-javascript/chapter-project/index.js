// Uses PokéAPI — free, no auth, CORS-friendly
const BASE = 'https://pokeapi.co/api/v2';

async function apiFetch(url, signal) {
  const res = await fetch(url, { signal });
  if (!res.ok) throw new Error(`${res.status} ${res.statusText} — ${url}`);
  return res.json();
}

// Serial: fetch ditto, then use its type to fetch the type's details
async function serialExample() {
  console.log('\n--- Serial (sequential) ---');
  const t0 = performance.now();
  const ditto = await apiFetch(`${BASE}/pokemon/ditto`);
  const typeName = ditto.types[0].type.name;
  const type = await apiFetch(`${BASE}/type/${typeName}`);
  console.log(`ditto type: ${typeName}, damage relations: ${Object.keys(type.damage_relations).length} entries`);
  console.log(`Serial took: ${(performance.now() - t0).toFixed(0)}ms`);
}

// Parallel: fetch three Pokémon at once
async function parallelExample() {
  console.log('\n--- Parallel (Promise.all) ---');
  const t0 = performance.now();
  const names = ['bulbasaur', 'charmander', 'squirtle'];
  const pokemon = await Promise.all(
    names.map(name => apiFetch(`${BASE}/pokemon/${name}`))
  );
  pokemon.forEach(p => console.log(`${p.name} — weight: ${p.weight}`));
  console.log(`Parallel took: ${(performance.now() - t0).toFixed(0)}ms`);
}

// allSettled: don't fail fast — get all results even if some reject
async function allSettledExample() {
  console.log('\n--- allSettled (partial failures) ---');
  const results = await Promise.allSettled([
    apiFetch(`${BASE}/pokemon/pikachu`),
    apiFetch(`${BASE}/pokemon/not-a-real-pokemon-xyz`),
    apiFetch(`${BASE}/pokemon/gengar`),
  ]);
  results.forEach((r, i) => {
    if (r.status === 'fulfilled') console.log(`[${i}] OK: ${r.value.name}`);
    else console.log(`[${i}] Error: ${r.reason.message}`);
  });
}

// AbortController: cancel a stale request
async function abortExample() {
  console.log('\n--- AbortController ---');
  const controller = new AbortController();
  setTimeout(() => controller.abort(), 50); // abort after 50ms
  try {
    await apiFetch(`${BASE}/pokemon/mewtwo`, controller.signal);
    console.log('Request completed');
  } catch (e) {
    console.log('Aborted or failed:', e.name); // AbortError
  }
}

(async () => {
  await serialExample();
  await parallelExample();
  await allSettledExample();
  await abortExample();
})();
