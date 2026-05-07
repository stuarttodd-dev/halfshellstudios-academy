const orders = [
  { id: 1,  customer: 'Alice', product: 'Course A', status: 'paid',    total: 2999, date: '2024-01' },
  { id: 2,  customer: 'Bob',   product: 'Course B', status: 'paid',    total: 1999, date: '2024-01' },
  { id: 3,  customer: 'Alice', product: 'Course C', status: 'refund',  total: 2999, date: '2024-02' },
  { id: 4,  customer: 'Carol', product: 'Course A', status: 'paid',    total: 2999, date: '2024-02' },
  { id: 5,  customer: 'Bob',   product: 'Course D', status: 'paid',    total: 4999, date: '2024-02' },
  { id: 6,  customer: 'Dave',  product: 'Course B', status: 'pending', total: 1999, date: '2024-03' },
  { id: 7,  customer: 'Alice', product: 'Course D', status: 'paid',    total: 4999, date: '2024-03' },
  { id: 8,  customer: 'Carol', product: 'Course C', status: 'paid',    total: 2999, date: '2024-03' },
];

// 1. Filter: paid orders only
const paid = orders.filter(o => o.status === 'paid');
console.log(`Paid orders: ${paid.length}`);

// 2. Map: extract totals
const totals = paid.map(o => o.total);
console.log('Totals (pence):', totals);

// 3. Reduce: sum
const grandTotal = paid.reduce((sum, o) => sum + o.total, 0);
console.log('Grand total:', new Intl.NumberFormat('en-GB', { style: 'currency', currency: 'GBP' }).format(grandTotal / 100));

// 4. groupBy (no lodash — plain reduce)
const byCustomer = paid.reduce((acc, o) => {
  (acc[o.customer] ??= []).push(o);
  return acc;
}, {});
console.log('\nOrders by customer:');
for (const [customer, customerOrders] of Object.entries(byCustomer)) {
  const spent = customerOrders.reduce((s, o) => s + o.total, 0);
  console.log(`  ${customer}: ${customerOrders.length} orders, £${(spent / 100).toFixed(2)}`);
}

// 5. Top-N customers by spend
const topCustomers = Object.entries(byCustomer)
  .map(([name, customerOrders]) => ({ name, total: customerOrders.reduce((s, o) => s + o.total, 0) }))
  .sort((a, b) => b.total - a.total)
  .slice(0, 3);
console.log('\nTop 3 customers:', topCustomers.map(c => `${c.name} (£${(c.total / 100).toFixed(2)})`));

// 6. Monthly breakdown
const byMonth = paid.reduce((acc, o) => {
  acc[o.date] ??= 0;
  acc[o.date] += o.total;
  return acc;
}, {});
console.log('\nMonthly revenue:', Object.entries(byMonth).map(([m, t]) => `${m}: £${(t / 100).toFixed(2)}`));
