// Event delegation: one listener on a parent handles clicks on any child.
//
// Why it works: DOM events bubble upward from the target element through
// every ancestor until they reach the document root. We attach one listener
// high up and inspect e.target to decide what to do.
//
// PHP mental model: like a single Laravel route catching /api/items/*
// rather than registering one route per item — one handler, many inputs.
//
// Paste this into your browser's DevTools console to see it in action,
// or include it in a <script> tag after your HTML.

document.addEventListener('click', (e) => {
  // .closest() walks up from the clicked element until it finds a match.
  // Returns null if nothing matches, so the guard bails out for plain clicks.
  const btn = e.target.closest('[data-action]');
  if (!btn) return;

  console.log('Action:', btn.dataset.action, '| Element:', btn);

  switch (btn.dataset.action) {
    case 'delete':
      console.log('Would delete item:', btn.dataset.id ?? '(no id)');
      break;
    case 'edit':
      console.log('Would edit item:', btn.dataset.id ?? '(no id)');
      break;
    default:
      console.log('Unknown action:', btn.dataset.action);
  }
});

// Any element added to the DOM *after* this listener runs is automatically
// handled — no need to re-attach listeners when content is dynamically added.
// Example buttons you could add in the console:
//   document.body.innerHTML += '<button data-action="delete" data-id="42">Delete 42</button>';
