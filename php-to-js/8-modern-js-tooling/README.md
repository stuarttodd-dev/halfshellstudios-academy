# Chapter 8 — Modern JS Tooling

This chapter covers the JavaScript toolchain: ESM modules, Vite, ESLint, and Prettier. The chapter project demonstrates ESM in Node.js; the Vite scaffold below is the browser-side equivalent.

## Scaffold a Vite project

```bash
npm create vite@latest my-project -- --template vanilla
cd my-project
npm install
npm run dev
```

### What each config file does

| File | Purpose |
|---|---|
| `package.json` | Declares dependencies, scripts, and the module format (`"type": "module"` for ESM) |
| `vite.config.js` | Tells Vite how to bundle: entry point, output dir, plugins, dev-server port |
| `.eslintrc` / `eslint.config.js` | Lint rules — catches bugs and enforces style before they ship |
| `.prettierrc` | Formatting rules — tabs vs spaces, semicolons, quote style |

### Key npm scripts

| Script | What it does |
|---|---|
| `npm run dev` | Starts Vite's dev server with HMR (hot module replacement) |
| `npm run build` | Bundles and tree-shakes for production into `dist/` |
| `npm run lint` | Runs ESLint across the project |
| `npm run format` | Runs Prettier and rewrites files in place |

## Run the chapter project (Node.js ESM demo)

```bash
node chapter-project/index.js
```

No dependencies — plain Node.js (v18+). Make sure `package.json` is present so Node treats `.js` files as ESM.
