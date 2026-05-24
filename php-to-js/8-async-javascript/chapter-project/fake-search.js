const DB = [
  { id: 1, title: 'Running shoes' },
  { id: 2, title: 'Merino socks' },
  { id: 3, title: 'Trail pack' },
];

function abortError() {
  const err = new Error('Aborted');
  err.name = 'AbortError';
  return err;
}

export function fakeSearch(query, { ms = 80, signal } = {}) {
  return new Promise((resolve, reject) => {
    if (signal?.aborted) {
      reject(abortError());
      return;
    }

    const onAbort = () => {
      clearTimeout(timer);
      reject(abortError());
    };

    signal?.addEventListener('abort', onAbort, { once: true });

    const timer = setTimeout(() => {
      signal?.removeEventListener('abort', onAbort);
      if (signal?.aborted) {
        reject(abortError());
        return;
      }
      if (query === 'fail') {
        reject(new Error('503'));
        return;
      }
      const q = query.toLowerCase();
      resolve(DB.filter((row) => row.title.toLowerCase().includes(q)));
    }, ms);
  });
}
