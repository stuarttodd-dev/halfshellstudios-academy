import { greet } from '@/lib/greet.js';

const appName = import.meta.env.VITE_APP_NAME ?? 'world';
const app = document.querySelector('#app');

if (app) {
  app.textContent = greet(appName);
}
