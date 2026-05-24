// Starting monolith — kept for comparison. Do not import in the refactored flow.
var API = 'https://api.example.com/orders';

async function go(e) {
  e.preventDefault();
  var n = document.querySelector('#name').value;
  var a = document.querySelector('#amount').value;
  if (n.length > 1) {
    if (parseFloat(a) > 0) {
      var body = { name: n, amount: parseFloat(a) * 100 };
      var r = await fetch(API, { method: 'POST', body: JSON.stringify(body) });
      if (r.status == 200) {
        document.querySelector('#ok').style.display = 'block';
      } else {
        document.querySelector('#err').innerText = 'Failed';
      }
    }
  }
}
