# Chapter 4 — Objects and Prototypes

This chapter covers JavaScript's object model: classes as syntax sugar over prototypes, private fields, `toJSON`/`fromJSON` round-trips, and walking the prototype chain. PHP developers will recognise the class syntax but will find the prototype mechanism underneath is quite different from PHP's vtable-based dispatch.

## Chapter project

A `Money` value object and a `Product` model with private fields, immutable `withTag()`, and JSON serialisation/deserialisation.

```bash
node chapter-project/index.js
```

## Snippets

```bash
node snippets/prototype-chain.js
```

| File | What it demonstrates |
|------|----------------------|
| `snippets/prototype-chain.js` | Class inheritance, prototype chain traversal, `instanceof` |
