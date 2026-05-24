export function formatCurrency(amount, currency = 'GBP') {
  if (typeof amount !== 'number' || Number.isNaN(amount)) {
    throw new TypeError('amount must be a number');
  }
  return new Intl.NumberFormat('en-GB', {
    style: 'currency',
    currency,
  }).format(amount);
}

export function truncate(text, maxLength = 100, ellipsis = '…') {
  if (text.length <= maxLength) return text;
  return text.slice(0, maxLength) + ellipsis;
}

export function slugify(text) {
  return text
    .toLowerCase()
    .trim()
    .replace(/[^a-z0-9\s-]/g, '')
    .replace(/[\s-]+/g, '-')
    .replace(/^-+|-+$/g, '');
}
