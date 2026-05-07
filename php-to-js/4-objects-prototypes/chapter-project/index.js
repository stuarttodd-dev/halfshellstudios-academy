// Money + Product — a class-shaped domain model with private fields.
//
// Private fields (#field) are enforced by the JS engine at runtime.
// PHP equivalent: private $field — same intent, different enforcement mechanism.
//
// Run with: node chapter-project/index.js

// ---------------------------------------------------------------------------
// Money — an immutable value object for currency amounts stored as pence.
// ---------------------------------------------------------------------------

class Money {
  #pence;

  constructor(pence) {
    if (!Number.isInteger(pence) || pence < 0) {
      throw new TypeError('Money requires a non-negative integer (pence)');
    }
    this.#pence = pence;
  }

  get pence() { return this.#pence; }

  // Returns a new Money — immutable, just like PHP's value objects should be.
  add(other) { return new Money(this.#pence + other.#pence); }

  format(currency = 'GBP') {
    return new Intl.NumberFormat('en-GB', { style: 'currency', currency })
      .format(this.#pence / 100);
  }

  // JSON.stringify calls toJSON() automatically when it exists.
  // Storing pence (integer) avoids floating-point serialisation issues.
  toJSON() { return this.#pence; }

  static fromJSON(pence) { return new Money(pence); }
}

// ---------------------------------------------------------------------------
// Product — an entity with immutable update helpers.
// ---------------------------------------------------------------------------

class Product {
  #id;
  #name;
  #price;
  #tags;

  constructor({ id, name, price, tags = [] }) {
    if (!id || !name) throw new TypeError('id and name are required');
    this.#id    = id;
    this.#name  = name;
    this.#price = price instanceof Money ? price : new Money(price);
    this.#tags  = [...tags]; // defensive copy
  }

  get id()    { return this.#id; }
  get name()  { return this.#name; }
  get price() { return this.#price; }
  get tags()  { return [...this.#tags]; } // defensive copy on read

  // Returns a new Product with the tag appended — the original is unchanged.
  // PHP equivalent: clone $product; $clone->tags[] = $tag;
  withTag(tag) {
    return new Product({
      id:    this.#id,
      name:  this.#name,
      price: this.#price,
      tags:  [...this.#tags, tag],
    });
  }

  toJSON() {
    return {
      id:    this.#id,
      name:  this.#name,
      price: this.#price.toJSON(), // delegates to Money#toJSON → integer pence
      tags:  this.#tags,
    };
  }

  // Accepts a raw JSON string or an already-parsed object.
  // PHP equivalent: new Product(json_decode($json, true))
  static fromJSON(raw) {
    const data = typeof raw === 'string' ? JSON.parse(raw) : raw;
    return new Product({ ...data, price: Money.fromJSON(data.price) });
  }

  toString() {
    const tagList = this.#tags.length ? `[${this.#tags.join(', ')}]` : '(no tags)';
    return `${this.#name} — ${this.#price.format()} ${tagList}`;
  }
}

// ---------------------------------------------------------------------------
// Demo
// ---------------------------------------------------------------------------

const p = new Product({
  id:    'p1',
  name:  'PHP to JS course',
  price: 2999,
  tags:  ['course', 'js'],
});

console.log('Original:  ', p.toString());

// withTag is non-destructive — p is unchanged
const withTag = p.withTag('best-seller');
console.log('With tag:  ', withTag.toString());
console.log('Original unchanged:', p.toString());

// JSON round-trip
const json = JSON.stringify(p);
console.log('JSON:      ', json);

const restored = Product.fromJSON(json);
console.log('Restored:  ', restored.toString());
console.log('Price pence:', restored.price.pence); // 2999

// Money arithmetic
const a = new Money(1999);
const b = new Money(1001);
console.log('Sum:       ', a.add(b).format()); // £30.00
