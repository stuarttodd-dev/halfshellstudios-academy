import { fakeSearch } from './fake-search.js';

export const STATUS = {
  IDLE: 'idle',
  LOADING: 'loading',
  OK: 'ok',
  ERROR: 'error',
  EMPTY: 'empty',
};

export function initialView() {
  return { status: STATUS.IDLE, query: '', results: [], error: null };
}

let latestId = 0;
let controller = null;

export async function executeSearch(query) {
  const id = ++latestId;
  controller?.abort();
  controller = new AbortController();

  if (!query.trim()) {
    return { status: STATUS.EMPTY, query, results: [], error: null };
  }

  const loadingView = { status: STATUS.LOADING, query, results: [], error: null };

  try {
    const results = await fakeSearch(query, { signal: controller.signal });
    if (id !== latestId) return loadingView;
    const status = results.length ? STATUS.OK : STATUS.EMPTY;
    return { status, query, results, error: null };
  } catch (err) {
    if (err.name === 'AbortError') return loadingView;
    if (id !== latestId) return loadingView;
    return { status: STATUS.ERROR, query, results: [], error: err.message };
  }
}

export function render(view) {
  if (view.status === STATUS.LOADING) return `loading: ${view.query}`;
  if (view.status === STATUS.ERROR) return `error: ${view.error}`;
  if (view.status === STATUS.EMPTY) return `empty: ${view.query || '(no query)'}`;
  if (view.status === STATUS.OK) {
    return `ok: ${view.results.map((row) => row.title).join(', ')}`;
  }
  return `idle`;
}
