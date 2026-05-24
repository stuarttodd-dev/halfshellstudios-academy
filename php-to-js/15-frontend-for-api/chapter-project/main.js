const deliverables = [
  { name: 'api client', ok: true },
  { name: 'router + auth', ok: true },
  { name: 'crud screens', ok: true },
  { name: 'vitest tests', ok: false },
  { name: 'HANDOFF.md', ok: false },
];

deliverables.forEach((item) => console.log(item.ok ? `PASS ${item.name}` : `TODO ${item.name}`));
const todo = deliverables.filter((item) => !item.ok).length;
console.log(todo ? 'project incomplete' : 'SPA handoff ready');
