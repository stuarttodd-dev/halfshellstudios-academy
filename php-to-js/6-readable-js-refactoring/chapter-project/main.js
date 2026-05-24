import { bindCheckout } from './features/checkout/bind-checkout.js';

const http = {
  post: async () => ({ ok: true, data: { orderId: 'ord_1' } }),
};

const ui = { successVisible: false, errorMessage: '' };
const onSubmit = bindCheckout({ http, ui });

onSubmit({ name: 'Ada', amount: '12.50' }).then(console.log);
