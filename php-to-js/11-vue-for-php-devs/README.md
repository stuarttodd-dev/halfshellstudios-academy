# Chapter 11 — Vue for PHP Developers

Vue Single File Components (SFCs) for PHP developers: reactivity, template syntax, computed properties, lifecycle hooks, and a realistic feature.

## Setup

These `.vue` files require a Vite + Vue project to run in a browser. Create one:

```bash
npm create vite@latest my-app -- --template vue
cd my-app
npm install
```

Then copy the `.vue` files from `chapter-project/` into `src/` and import them in `App.vue`.

## Files

| File | What it shows |
|------|---------------|
| `chapter-project/PokemonList.vue` | Full feature: fetch list, search filter, toggle favourites — all in one SFC |
| `snippets/reactivity.js` | Vue's `ref()` explained as a plain JS getter/setter with subscribers |

## Key ideas for PHP developers

| PHP | Vue |
|-----|-----|
| `$variable` in Blade | `{{ variable }}` in template |
| `@if` / `@foreach` | `v-if` / `v-for` |
| Controller passes data to view | `<script setup>` declares reactive state |
| Form POST | `v-model` + `@submit.prevent` |
| Livewire reactive property | `ref()` / `reactive()` |

## Running the reactivity snippet

```bash
node snippets/reactivity.js
```
