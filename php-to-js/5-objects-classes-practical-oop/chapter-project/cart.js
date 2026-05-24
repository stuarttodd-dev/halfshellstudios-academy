export class LineItem {
  constructor(sku, qty, unitPrice) {
    this.sku = sku;
    this.qty = qty;
    this.unitPrice = unitPrice;
  }

  lineTotal() {
    return this.qty * this.unitPrice;
  }

  withQty(qty) {
    return new LineItem(this.sku, qty, this.unitPrice);
  }

  toJSON() {
    return { sku: this.sku, qty: this.qty, unit_price: this.unitPrice };
  }

  static fromJSON(raw) {
    const d = typeof raw === 'string' ? JSON.parse(raw) : raw;
    const sku = String(d.sku ?? '').trim();
    if (!sku) throw new Error('sku required');
    return new LineItem(sku, Number(d.qty ?? 0), Number(d.unit_price ?? 0));
  }
}

export class Cart {
  constructor() {
    this.lines = [];
  }

  addLine(line) {
    this.lines.push(line);
  }

  subtotal() {
    return this.lines.reduce((sum, line) => sum + line.lineTotal(), 0);
  }

  toJSON() {
    return { lines: this.lines.map((l) => l.toJSON()) };
  }

  static fromJSON(raw) {
    const d = typeof raw === 'string' ? JSON.parse(raw) : raw;
    const cart = new Cart();
    for (const row of d.lines ?? []) {
      cart.addLine(LineItem.fromJSON(row));
    }
    return cart;
  }
}
