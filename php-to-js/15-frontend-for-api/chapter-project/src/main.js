import { createApp } from 'vue';
import App from './App.vue';
import { bootstrapAuth } from './auth.js';
import { router } from './router.js';

bootstrapAuth().then(() => {
  createApp(App).use(router).mount('#app');
});
