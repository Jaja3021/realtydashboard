const PROPERTY_TYPES = ['House and Lot', 'Condominium', 'Lot Only', 'Townhouse', 'Commercial'];
const EVENT_TYPES = ['meeting', 'tripping', 'manning', 'pks'];
const EVENT_TYPE_LABELS = { meeting: 'Meeting', tripping: 'Tripping', manning: 'Manning', pks: 'PKS' };

function peso(n) {
  return '₱' + Math.round(Number(n) || 0).toLocaleString();
}

function formatDate(isoDate) {
  const d = new Date(isoDate + 'T00:00:00');
  return d.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function escapeHtml(str) {
  const div = document.createElement('div');
  div.textContent = str == null ? '' : String(str);
  return div.innerHTML;
}

async function api(path, options) {
  const res = await fetch(path, options);
  const data = await res.json().catch(() => ({}));
  if (!res.ok) {
    const err = new Error(data.error || (data.errors && data.errors.join(' ')) || 'Request failed');
    err.data = data;
    throw err;
  }
  return data;
}
