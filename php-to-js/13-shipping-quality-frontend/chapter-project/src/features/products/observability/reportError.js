export function reportError(payload) {
  if (import.meta.env.DEV) {
    console.error('report', payload);
    return payload;
  }
  return payload;
}
