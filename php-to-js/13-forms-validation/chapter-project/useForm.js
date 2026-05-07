// useForm — framework-agnostic form state manager
// Equivalent to Laravel's Form Request, but client-side

import { z } from 'zod'; // npm install zod

export function useForm(schema, initialValues) {
  let values = { ...initialValues };
  let errors = {};
  let touched = {};
  let submitting = false;
  const subscribers = new Set();

  function notify() { subscribers.forEach(fn => fn(getState())); }
  function getState() {
    return { values: { ...values }, errors: { ...errors }, touched: { ...touched }, submitting };
  }

  function setField(field, value) {
    values[field] = value;
    touched[field] = true;
    validateField(field, value);
    notify();
  }

  function validateField(field, value) {
    const fieldSchema = schema.shape?.[field];
    if (!fieldSchema) return;
    const result = fieldSchema.safeParse(value);
    errors[field] = result.success ? undefined : result.error.errors[0].message;
  }

  function validateAll() {
    const result = schema.safeParse(values);
    if (result.success) {
      errors = {};
      return { ok: true, data: result.data };
    }
    errors = {};
    result.error.errors.forEach(e => {
      const field = e.path[0];
      if (field) errors[field] ??= e.message;
    });
    return { ok: false };
  }

  async function submit(handler) {
    touched = Object.fromEntries(Object.keys(values).map(k => [k, true]));
    const { ok, data } = validateAll();
    notify();
    if (!ok) return false;
    submitting = true;
    notify();
    try {
      await handler(data);
      return true;
    } finally {
      submitting = false;
      notify();
    }
  }

  function subscribe(fn) { subscribers.add(fn); return () => subscribers.delete(fn); }
  function reset() { values = { ...initialValues }; errors = {}; touched = {}; notify(); }

  return { setField, submit, reset, subscribe, getState };
}
