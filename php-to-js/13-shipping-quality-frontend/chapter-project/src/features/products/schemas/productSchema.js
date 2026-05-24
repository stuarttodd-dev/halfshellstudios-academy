export const productSchema = {
  name: {
    rules: [(value) => (String(value ?? '').trim() ? null : 'Required')],
  },
  price: {
    rules: [(value) => (Number(value) >= 0 ? null : 'Must be >= 0')],
  },
};

export function validateSchema(schema, values) {
  const errors = {};
  for (const [field, config] of Object.entries(schema)) {
    for (const rule of config.rules) {
      const message = rule(values[field], values);
      if (message) {
        errors[field] = [message];
        break;
      }
    }
  }
  return errors;
}
