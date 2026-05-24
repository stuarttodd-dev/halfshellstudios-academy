import { todosApi } from './todos-api.js';

try {
  const list = await todosApi.list();
  console.log('list', list.length);

  const one = await todosApi.get(1);
  console.log('get', one.id, one.title);

  const created = await todosApi.create({ title: 'CRUD demo', completed: false, userId: 1 });
  console.log('create', created.id);

  const patched = await todosApi.update(created.id, { completed: true });
  console.log('update', patched.completed);

  await todosApi.remove(created.id);
  console.log('delete ok');
} catch (err) {
  console.error(err.message);
}
