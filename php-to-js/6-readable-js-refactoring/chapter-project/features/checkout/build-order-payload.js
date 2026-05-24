export const PENCE_MULTIPLIER = 100;

export function buildOrderPayload(name, amount) {
  return {
    name: name.trim(),
    amount: Math.round(Number(amount) * PENCE_MULTIPLIER),
  };
}
