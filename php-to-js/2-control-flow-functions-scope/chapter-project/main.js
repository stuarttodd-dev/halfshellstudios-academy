import { parseMoney, buildReceipt } from './receipt.js';

const raw = [
  { label: 'Plan', amount: '19.99', status: 'billable' },
  { label: 'Trial', amount: '0', status: 'billable' },
  { label: 'Note', amount: '5', status: 'draft' },
  { label: 'Bad', amount: 'oops', status: 'billable' },
];

const lines = raw.map((row) => {
  const parsed = parseMoney(row.amount);
  if (!parsed.ok) return { ...row, amount: 0, parseError: parsed.error };
  return { ...row, amount: parsed.amount, parseError: null };
});

const receipt = buildReceipt(lines.filter((l) => !l.parseError));
console.log(receipt);
lines.filter((l) => l.parseError).forEach((l) => console.log('skip', l.label, l.parseError));
