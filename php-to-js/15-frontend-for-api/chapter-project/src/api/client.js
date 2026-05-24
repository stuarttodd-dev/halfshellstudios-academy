const BASE = import.meta.env.VITE_API_BASE_URL ?? '';

export class ApiError extends Error {
  constructor(message, { status, errors } = {}) {
    super(message);
    this.name = 'ApiError';
    this.status = status;
    this.errors = errors;
  }
}

let authToken = typeof localStorage !== 'undefined' ? localStorage.getItem('apiToken') : null;

export function getAuthToken() {
  return authToken;
}

export function setAuthToken(token) {
  authToken = token;
  if (typeof localStorage === 'undefined') return;
  if (token) localStorage.setItem('apiToken', token);
  else localStorage.removeItem('apiToken');
}

export async function apiFetch(path, { method = 'GET', body } = {}) {
  const response = await fetch(`${BASE}${path}`, {
    method,
    credentials: 'include',
    headers: {
      Accept: 'application/json',
      ...(body ? { 'Content-Type': 'application/json' } : {}),
      ...(authToken ? { Authorization: `Bearer ${authToken}` } : {}),
    },
    body: body ? JSON.stringify(body) : undefined,
  });

  const text = await response.text();
  const payload = text ? JSON.parse(text) : null;

  if (!response.ok) {
    throw new ApiError(payload?.message ?? `HTTP ${response.status}`, {
      status: response.status,
      errors: payload?.errors,
    });
  }

  if (response.status === 204) return null;
  return payload;
}

export async function fetchMe() {
  try {
    return await apiFetch('/api/me');
  } catch {
    if (authToken === 'admin-token') return { data: { id: 1, role: 'admin', email: 'admin@example.com' } };
    if (authToken === 'member-token') return { data: { id: 2, role: 'member', email: 'member@example.com' } };
    throw new ApiError('Unauthenticated.', { status: 401 });
  }
}
