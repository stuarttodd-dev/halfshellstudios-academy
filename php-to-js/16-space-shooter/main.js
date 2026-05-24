const skills = [
  { name: 'read real TS repo', game: true, product: true },
  { name: 'extract service class', game: true, product: true },
  { name: 'memorise Phaser API', game: true, product: false },
];

skills.forEach((skill) => {
  console.log(skill.name, skill.product ? 'transfers' : 'game-only');
});
