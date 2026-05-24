<script setup>
import { useRoute, useRouter } from 'vue-router';
import { login } from '../auth.js';

const route = useRoute();
const router = useRouter();

function signIn() {
  login();
  const redirect = route.query.redirect ?? '/products';
  router.push(String(redirect));
}
</script>

<template>
  <section class="login">
    <h1>Sign in</h1>
    <p>Mock login for the product admin handoff demo.</p>
    <button type="button" @click="signIn">Continue as dev user</button>
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
  padding: 0.6rem 1rem;
  background: #6366f1;
  color: #fff;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}
</style>
