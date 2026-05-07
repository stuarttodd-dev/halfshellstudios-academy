<script setup>
import { ref, computed, onMounted } from 'vue'

const pokemon = ref([])
const favourites = ref([])
const search = ref('')
const loading = ref(false)
const error = ref(null)

const filtered = computed(() =>
  pokemon.value.filter(p => p.name.includes(search.value.toLowerCase()))
)

const isFavourite = (name) => favourites.value.includes(name)

function toggleFavourite(name) {
  if (isFavourite(name)) {
    favourites.value = favourites.value.filter(f => f !== name)
  } else {
    favourites.value.push(name)
  }
}

onMounted(async () => {
  loading.value = true
  try {
    const res = await fetch('https://pokeapi.co/api/v2/pokemon?limit=20')
    const data = await res.json()
    pokemon.value = data.results
  } catch (e) {
    error.value = e.message
  } finally {
    loading.value = false
  }
})
</script>

<template>
  <div class="pokemon-list">
    <h1>Pokédex</h1>
    <input v-model="search" placeholder="Filter Pokémon…" />

    <p v-if="loading">Loading…</p>
    <p v-else-if="error" class="error">{{ error }}</p>

    <ul v-else>
      <li v-for="p in filtered" :key="p.name">
        <span>{{ p.name }}</span>
        <button @click="toggleFavourite(p.name)">
          {{ isFavourite(p.name) ? '★' : '☆' }}
        </button>
      </li>
    </ul>

    <div v-if="favourites.length">
      <h2>Favourites</h2>
      <ul>
        <li v-for="name in favourites" :key="name">
          {{ name }}
          <button @click="toggleFavourite(name)">Remove</button>
        </li>
      </ul>
    </div>
  </div>
</template>
