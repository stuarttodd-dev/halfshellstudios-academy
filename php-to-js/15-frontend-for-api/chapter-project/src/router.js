import { createRouter, createWebHashHistory } from 'vue-router';
import { authGuard } from './auth.js';
import ProductCreatePage from './features/products/ProductCreatePage.vue';
import ProductEditPage from './features/products/ProductEditPage.vue';
import ProductListPage from './features/products/ProductListPage.vue';
import LoginPage from './views/LoginPage.vue';

export const router = createRouter({
  history: createWebHashHistory(),
  routes: [
    { path: '/', redirect: '/products' },
    { path: '/login', component: LoginPage },
    { path: '/products', component: ProductListPage, meta: { requiresAuth: true } },
    { path: '/products/new', component: ProductCreatePage, meta: { requiresAuth: true } },
    { path: '/products/:id/edit', component: ProductEditPage, meta: { requiresAuth: true } },
  ],
});

router.beforeEach((to) => authGuard(to));
