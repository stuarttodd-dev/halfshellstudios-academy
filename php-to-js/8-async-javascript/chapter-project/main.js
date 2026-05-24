const STATUS = { LOADING: 'loading', OK: 'ok', ERROR: 'error', EMPTY: 'empty' };

function fakeSearch(query, ms) {
  const db = [{ title: 'Running shoes' }, { title: 'Trail pack' }];
  return new Promise((resolve, reject) => {
    setTimeout(() => {
      if (query === 'fail') reject(new Error('503'));
      resolve(db.filter((row) => row.title.toLowerCase().includes(query.toLowerCase())));
    }, ms);
  });
}

let latestId = 0;

async function runSearch(query) {
  const id = ++latestId;
  if (!query) return { status: STATUS.EMPTY, n: 0 };
  try {
    const slow = query === 'run';
    const results = await fakeSearch(query, slow ? 120 : 30);
    if (id !== latestId) return { status: 'stale', n: results.length };
    return { status: results.length ? STATUS.OK : STATUS.EMPTY, n: results.length };
  } catch (e) {
    return { status: STATUS.ERROR, msg: e.message };
  }
}

runSearch('ru');
runSearch('run');
setTimeout(() => runSearch('trail').then((view) => console.log('final', view)), 150);
