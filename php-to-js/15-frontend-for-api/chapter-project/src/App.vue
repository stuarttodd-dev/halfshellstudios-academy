<script setup>
import { RouterLink, RouterView } from 'vue-router';
import { logout, user } from './auth.js';
</script>

<template>
  <div class="app">
    <nav v-if="user" class="nav">
      <RouterLink to="/products">Products</RouterLink>
      <span>{{ user.email }} ({{ user.role }})</span>
      <button type="button" @click="logout">Log out</button>
    </nav>
    <RouterView />
  </div>
</template>

<style>
body {
  margin: 0;
  background: #f9fafb;
}
.nav {
  display: flex;
  gap: 1rem;
  align-items: center;
  padding: 0.75rem 1.5rem;
  background: #fff;
  border-bottom: 1px solid #e5e7eb;
  font-family: system-ui, sans-serif;
}
</style>
