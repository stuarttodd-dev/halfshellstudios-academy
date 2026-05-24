import { validateCheckoutFields } from './validate-checkout.js';
import { buildOrderPayload } from './build-order-payload.js';
import { submitOrder } from './submit-order.js';
import { showError, showSuccess } from './render-checkout.js';

export function bindCheckout({ http, ui }) {
  return async function onSubmit(fields) {
    const validation = validateCheckoutFields(fields.name, fields.amount);
    if (!validation.ok) {
      showError(ui, validation.message);
      return validation;
    }

    const payload = buildOrderPayload(fields.name, fields.amount);

    try {
      const data = await submitOrder(payload, http);
      showSuccess(ui);
      return { ok: true, orderId: data.orderId, payload };
    } catch {
      showError(ui, 'Failed');
      return { ok: false, message: 'Failed' };
    }
  };
}
