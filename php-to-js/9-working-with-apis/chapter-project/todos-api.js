const BASE = 'https://jsonplaceholder.typicode.com';

export async function apiFetch(path, init = {}) {
  const res = await fetch(`${BASE}${path}`, {
    headers: {
      Accept: 'application/json',
      ...(init.body ? { 'Content-Type': 'application/json' } : {}),
      ...(init.headers ?? {}),
    },
    ...init,
    ...(init.body ? { body: JSON.stringify(init.body) } : {}),
  });

  if (!res.ok) {
    const text = await res.text();
    throw Object.assign(new Error(`HTTP ${res.status}`), { status: res.status, body: text });
  }

  if (res.status === 204) return null;

  const text = await res.text();
  return text ? JSON.parse(text) : null;
}

export const todosApi = {
  list: () => apiFetch('/todos?_limit=3'),
  get: (id) => apiFetch(`/todos/${id}`),
  create: (body) => apiFetch('/todos', { method: 'POST', body }),
  update: (id, body) => apiFetch(`/todos/${id}`, { method: 'PATCH', body }),
  remove: (id) => apiFetch(`/todos/${id}`, { method: 'DELETE' }),
};
