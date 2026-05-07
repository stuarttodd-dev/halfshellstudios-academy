// A simple but production-shaped API client.
// Compare to Guzzle / Laravel's Http facade.

class ApiError extends Error {
  constructor(message, { status, url, body } = {}) {
    super(message);
    this.name = 'ApiError';
    this.status = status;
    this.url = url;
    this.body = body;
  }
}

async function withRetry(fn, { retries = 2, delay = 300 } = {}) {
  let lastError;
  for (let attempt = 0; attempt <= retries; attempt++) {
    try {
      return await fn();
    } catch (e) {
      lastError = e;
      if (attempt < retries) {
        await new Promise(r => setTimeout(r, delay * (attempt + 1)));
        console.log(`  Retry ${attempt + 1}/${retries}...`);
      }
    }
  }
  throw lastError;
}

class HttpClient {
  #baseUrl;
  #headers;

  constructor(baseUrl, headers = {}) {
    this.#baseUrl = baseUrl.replace(/\/$/, '');
    this.#headers = { 'Content-Type': 'application/json', ...headers };
  }

  async #request(method, path, { body, signal } = {}) {
    const url = `${this.#baseUrl}${path}`;
    const res = await fetch(url, {
      method,
      headers: this.#headers,
      body: body ? JSON.stringify(body) : undefined,
      signal,
    });
    const text = await res.text();
    const data = text ? JSON.parse(text) : null;
    if (!res.ok) throw new ApiError(`${method} ${url} → ${res.status}`, { status: res.status, url, body: data });
    return data;
  }

  get(path, opts)         { return this.#request('GET',    path, opts); }
  post(path, body, opts)  { return this.#request('POST',   path, { body, ...opts }); }
  put(path, body, opts)   { return this.#request('PUT',    path, { body, ...opts }); }
  delete(path, opts)      { return this.#request('DELETE', path, opts); }
}

const pokeClient = new HttpClient('https://pokeapi.co/api/v2');
const httpbin    = new HttpClient('https://httpbin.org');

(async () => {
  // GET — real data
  const pikachu = await pokeClient.get('/pokemon/pikachu');
  console.log('GET pikachu:', pikachu.name, '— abilities:', pikachu.abilities.map(a => a.ability.name).join(', '));

  // Parallel GET
  const [bulba, charm] = await Promise.all([
    pokeClient.get('/pokemon/bulbasaur'),
    pokeClient.get('/pokemon/charmander'),
  ]);
  console.log('Parallel:', bulba.name, charm.name);

  // POST — httpbin echoes it back
  const posted = await httpbin.post('/post', { user: 'php-dev', course: 'php-to-js' });
  console.log('POST echoed:', posted.json);

  // Error handling
  try {
    await pokeClient.get('/pokemon/not-real-xyz');
  } catch (e) {
    if (e instanceof ApiError) console.log('ApiError caught:', e.status, e.url);
  }

  // Retry demo (will retry on network error)
  console.log('\nRetry demo (intentional bad URL):');
  try {
    await withRetry(() => pokeClient.get('/pokemon/will-fail-xyz'), { retries: 1, delay: 100 });
  } catch (e) {
    console.log('Failed after retries:', e.status);
  }
})();
