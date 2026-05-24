<script setup>
import { reactive } from 'vue';

const props = defineProps({
  fieldErrors: {
    type: Object,
    default: () => ({}),
  },
  create: {
    type: Function,
    required: true,
  },
});

const emit = defineEmits(['created']);

const form = reactive({ name: '', price: '' });

async function onSubmit() {
  const ok = await props.create({
    name: form.name,
    price: Number(form.price),
  });
  if (!ok) return;
  form.name = '';
  form.price = '';
  emit('created');
}
</script>

<template>
  <form class="create-form" @submit.prevent="onSubmit">
    <h2>Add product</h2>
    <div class="field">
      <label for="name">Name</label>
      <input id="name" v-model="form.name" type="text" name="name" />
      <p v-if="fieldErrors.name" class="error">{{ fieldErrors.name[0] }}</p>
    </div>
    <div class="field">
      <label for="price">Price</label>
      <input id="price" v-model="form.price" type="number" step="0.01" min="0" name="price" />
      <p v-if="fieldErrors.price" class="error">{{ fieldErrors.price[0] }}</p>
    </div>
    <button type="submit">Create</button>
  </form>
</template>

<style scoped>
.create-form {
  margin-top: 1.5rem;
  padding-top: 1.5rem;
  border-top: 1px solid #e5e7eb;
}
.field {
  margin-bottom: 0.75rem;
}
label {
  display: block;
  font-size: 0.875rem;
  font-weight: 600;
  margin-bottom: 0.25rem;
}
input {
  width: 100%;
  padding: 0.5rem 0.65rem;
  border: 1px solid #d1d5db;
  border-radius: 4px;
}
.error {
  color: #dc2626;
  font-size: 0.8rem;
  margin: 0.25rem 0 0;
}
button {
  padding: 0.5rem 1rem;
  background: #6366f1;
  color: #fff;
  border: none;
  border-radius: 4px;
  cursor: pointer;
}
</style>
