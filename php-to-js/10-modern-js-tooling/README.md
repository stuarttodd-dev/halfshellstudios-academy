# Chapter 10 — Modern JS Tooling

A minimal production-ready front-end toolchain: Vite, ESLint, Prettier, Vitest, and a `greet` module that proves the pipeline works.

## Chapter project

| File | Purpose |
|------|---------|
| `chapter-project/package.json` | `dev`, `build`, `lint`, `test`, `format:check`, `check` scripts |
| `chapter-project/vite.config.js` | `@` alias → `resources/js`, build to `public/build` |
| `chapter-project/eslint.config.js` | `no-unused-vars`, `eqeqeq` |
| `chapter-project/.prettierrc` | Team formatting |
| `chapter-project/.nvmrc` | Node LTS version |
| `chapter-project/.env.example` | `VITE_APP_NAME` only — no secrets |
| `chapter-project/resources/js/app.js` | Entry — imports `@/lib/greet.js` |
| `chapter-project/resources/js/lib/greet.js` | Pure function + named export |
| `chapter-project/resources/js/lib/greet.test.js` | Vitest spec |
| `chapter-project/main.js` | Console sandbox demo (no build step) |

### How to run — console demo

From the **repository root**:

```bash
node php-to-js/10-modern-js-tooling/chapter-project/main.js
```

Expected output:

```
Hello, world
validation name is required
CI steps lint → format:check → test → build
```

### How to run — full toolchain

```bash
cd php-to-js/10-modern-js-tooling/chapter-project
npm install
npm run dev       # local dev server
npm run check     # lint + format + test + build
```

Copy `.env.example` to `.env` before `npm run dev` if you want a custom app name in the browser.

### CI checklist

1. `npm ci`
2. `npm run check`
3. Upload `public/build` artefact, or build on deploy (pick one strategy and stick with it)

### Script summary

| Script | One-line purpose |
|--------|------------------|
| `dev` | Start Vite dev server with hot reload |
| `build` | Bundle for production into `public/build` |
| `lint` | Run ESLint on `resources/js` |
| `test` | Run Vitest once |
| `format:check` | Fail if Prettier would change anything |
| `check` | Full gate: lint → format → test → build |

Wire into Laravel `@vite` in a real app and commit the lockfile.
