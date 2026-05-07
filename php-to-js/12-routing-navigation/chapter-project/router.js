// Minimal client-side router — this is what Vue Router does internally
// Run in a browser (not Node) or adapt for Node with a mock history API

class Router {
  #routes = [];
  #currentRoute = null;
  #listeners = [];

  add(path, component) {
    // Convert /users/:id to a regex with named groups
    const pattern = path.replace(/:([^/]+)/g, '(?<$1>[^/]+)');
    this.#routes.push({ path, regex: new RegExp(`^${pattern}$`), component });
    return this;
  }

  resolve(pathname) {
    for (const route of this.#routes) {
      const m = pathname.match(route.regex);
      if (m) return { ...route, params: m.groups ?? {} };
    }
    return null;
  }

  navigate(path, { replace = false } = {}) {
    const route = this.resolve(path);
    if (!route) { console.warn('No route for', path); return; }
    this.#currentRoute = { ...route, path };
    this.#listeners.forEach(fn => fn(this.#currentRoute));
  }

  onChange(fn) { this.#listeners.push(fn); }
  get current() { return this.#currentRoute; }
}

// Usage
const router = new Router()
  .add('/',                () => '<Home/>')
  .add('/pokemon',         () => '<PokemonList/>')
  .add('/pokemon/:name',   ({ name }) => `<PokemonDetail name="${name}"/>`)
  .add('/404',             () => '<NotFound/>');

router.onChange(route => {
  console.log('Route changed:', route.path, 'params:', route.params);
  console.log('Component:', route.component(route.params));
});

router.navigate('/');
router.navigate('/pokemon');
router.navigate('/pokemon/pikachu');
router.navigate('/pokemon/mewtwo');
router.navigate('/not-defined');
