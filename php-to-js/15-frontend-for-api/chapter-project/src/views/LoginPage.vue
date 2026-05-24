<script setup>
import { useRoute, useRouter } from 'vue-router';
import { login } from '../auth.js';

const route = useRoute();
const router = useRouter();

async function signIn(role) {
  const token = role === 'admin' ? 'admin-token' : 'member-token';
  await login(token);
  const redirect = route.query.redirect ?? '/products';
  router.push(String(redirect));
}
</script>

<template>
  <section class="login">
    <h1>Sign in</h1>
    <p>Use the same tokens as chapter 14's Products API.</p>
    <button type="button" @click="signIn('admin')">Continue as admin</button>
    <button type="button" class="secondary" @click="signIn('member')">Continue as member</button>
  </section>
</template>

<style scoped>
.login {
  max-width: 420px;
  margin: 3rem auto;
  padding: 1.5rem;
  font-family: system-ui, sans-serif;
}
button {
  display: block;
  width: 100%;
  margin-top: 0.75rem;
  padding: 0.6rem 1rem;
  background: #6366f1;
  color: #fff;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}
.secondary {
  background: #e5e7eb;
  color: #111827;
}
</style>
