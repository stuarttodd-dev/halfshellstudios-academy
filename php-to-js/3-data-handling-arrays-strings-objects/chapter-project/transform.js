export function normaliseLine(raw) {
  return {
    sku: String(raw.sku ?? raw.product_code ?? ''),
    qty: Math.max(0, Number(raw.qty ?? raw.quantity ?? 0)),
    label: String(raw.label ?? 'Unnamed'),
  };
}

export function normaliseOrder(raw) {
  return {
    id: Number(raw.id ?? 0),
    status: String(raw.status ?? 'draft'),
    total: Number(raw.total ?? 0),
    lines: (raw.lines ?? []).map(normaliseLine),
  };
}

export function toTableRows(apiPayload) {
  const rawRows = Array.isArray(apiPayload.data) ? apiPayload.data : [];
  return rawRows
    .map(normaliseOrder)
    .filter((o) => o.id > 0)
    .map((o) => ({
      id: o.id,
      statusLabel: o.status.toUpperCase(),
      lineCount: o.lines.length,
      totalDisplay: `£${o.total.toFixed(2)}`,
    }));
}
