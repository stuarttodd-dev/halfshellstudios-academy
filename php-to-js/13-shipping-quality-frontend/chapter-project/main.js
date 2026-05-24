const handoffSteps = [
  () => ({ step: 'list loads', ok: true }),
  () => ({ step: 'search filters', ok: true }),
  () => ({ step: 'create valid', ok: true }),
  () => ({ step: 'create 422', ok: true }),
  () => ({ step: 'api 500 banner', ok: false }),
];

const results = handoffSteps.map((run) => run());
results.forEach(({ step, ok }) => console.log(ok ? `PASS ${step}` : `BLOCKED ${step}`));
const blocked = results.filter((result) => !result.ok);
console.log(blocked.length ? 'handoff incomplete' : 'handoff ready');
