export async function submitOrder(payload, http) {
  const response = await http.post('/orders', payload);
  if (!response.ok) throw new Error('submit failed');
  return response.data;
}
