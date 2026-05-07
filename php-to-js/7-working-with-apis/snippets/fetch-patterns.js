// Tri-state: idle → loading → success | error
// This is the mental model behind React Query, SWR, Pinia's async actions.

class FetchState {
  status = 'idle'; // 'idle' | 'loading' | 'success' | 'error'
  data = null;
  error = null;

  async fetch(url) {
    this.status = 'loading';
    try {
      const res = await fetch(url);
      if (!res.ok) throw new Error(`${res.status}`);
      this.data = await res.json();
      this.status = 'success';
    } catch (e) {
      this.error = e;
      this.status = 'error';
    }
    return this;
  }
}

const state = new FetchState();
await state.fetch('https://pokeapi.co/api/v2/pokemon/mewtwo');
console.log('status:', state.status);
if (state.status === 'success') console.log('name:', state.data.name);
if (state.status === 'error')   console.log('error:', state.error.message);
