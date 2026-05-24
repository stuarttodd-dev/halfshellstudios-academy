export function validateCheckoutFields(name, amount) {
  if (!name || name.trim().length < 2) {
    return { ok: false, message: 'Name too short.' };
  }
  const n = Number(amount);
  if (!Number.isFinite(n) || n <= 0) {
    return { ok: false, message: 'Invalid amount.' };
  }
  return { ok: true };
}
