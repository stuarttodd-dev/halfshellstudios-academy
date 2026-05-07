# Chapter 13 — Forms and Validation

Framework-agnostic form state management with Zod schema validation — the client-side equivalent of Laravel Form Requests.

## Setup and run

```bash
cd chapter-project
npm install
npm start
```

This runs `registration-demo.js` with Node. Requires Node 18+ (uses top-level `await`).

## Files

| File | What it shows |
|------|---------------|
| `chapter-project/useForm.js` | Reusable form composable: field state, per-field validation, touched tracking, submit handler |
| `chapter-project/registration-demo.js` | Demo using `useForm` with a Zod schema: email, password, confirm-password |
| `chapter-project/package.json` | `zod` dependency |

## Key ideas for PHP developers

| Laravel | Client-side (`useForm`) |
|---------|------------------------|
| `FormRequest::rules()` | `z.object({ ... })` schema |
| `$request->validated()` | `data` from `submit(handler)` |
| `$errors->first('email')` | `errors.email` |
| `old('email')` | `values.email` |
| Field `required` | `z.string().min(1, ...)` |

## Why Zod?

Zod gives you the same parse-and-validate pattern as Laravel's validator, but in TypeScript/JavaScript. You define the shape once and get type inference, error messages, and refinements (cross-field rules like password confirmation) for free.
