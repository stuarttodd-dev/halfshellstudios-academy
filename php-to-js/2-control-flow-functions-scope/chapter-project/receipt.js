export function parseMoney(value) {
  if (value === '' || value == null) return { ok: false, error: 'Amount required' };
  const n = Number(value);
  if (Number.isNaN(n)) return { ok: false, error: 'Amount must be a number' };
  if (n < 0) return { ok: false, error: 'Amount cannot be negative' };
  return { ok: true, amount: n };
}

export function formatCurrency(amount) {
  return `£${amount.toFixed(2)}`;
}

export function formatLine(line) {
  return `${line.label}: ${formatCurrency(line.amount)}`;
}

export function buildReceipt(lines) {
  const billable = lines.filter((l) => l.status === 'billable');
  const rows = billable.map((line) => ({
    display: formatLine(line),
  }));
  const total = billable.reduce((sum, line) => sum + line.amount, 0);
  return { rows, total: formatCurrency(total) };
}
