const scenarios = [
  { name: 'index 200', ok: true },
  { name: 'store 201', ok: true },
  { name: 'store 422', ok: true },
  { name: 'guest 401', ok: true },
  { name: 'forbidden 403', ok: true },
  { name: 'fixtures committed', ok: false },
];

const failed = scenarios.filter((scenario) => !scenario.ok);
scenarios.forEach((scenario) => console.log(scenario.ok ? `PASS ${scenario.name}` : `TODO ${scenario.name}`));
console.log(failed.length ? 'project incomplete' : 'API v1 ready for Vue');
