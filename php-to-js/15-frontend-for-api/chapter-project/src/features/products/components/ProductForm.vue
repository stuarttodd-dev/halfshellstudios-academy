<script setup>
import { reactive, watch } from 'vue';

const props = defineProps({
  modelValue: {
    type: Object,
    default: () => ({ name: '', price: '', sku: '' }),
  },
  fieldErrors: {
    type: Object,
    default: () => ({}),
  },
  submitting: {
    type: Boolean,
    default: false,
  },
  submitLabel: {
    type: String,
    default: 'Save',
  },
});

const emit = defineEmits(['submit']);

const form = reactive({ name: '', price: '', sku: '' });

watch(
  () => props.modelValue,
  (value) => {
    form.name = value.name ?? '';
    form.price = value.price ?? '';
    form.sku = value.sku ?? '';
  },
  { immediate: true },
);

function onSubmit() {
  emit('submit', {
    name: form.name,
    price: Number(form.price),
    sku: form.sku || null,
  });
}
</script>

<template>
  <form class="product-form" @submit.prevent="onSubmit">
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
    <div class="field">
      <label for="sku">SKU (optional)</label>
      <input id="sku" v-model="form.sku" type="text" name="sku" />
    </div>
    <button type="submit" :disabled="submitting">
      {{ submitting ? 'Saving…' : submitLabel }}
    </button>
  </form>
</template>

<style scoped>
.field {
  margin-bottom: 0.75rem;
}
label {
  display: block;
  font-weight: 600;
  margin-bottom: 0.25rem;
}
input {
  width: 100%;
  padding: 0.5rem 0.65rem;
}
.error {
  color: #dc2626;
  font-size: 0.8rem;
}
button[disabled] {
  opacity: 0.7;
}
</style>
