<?php
require_once __DIR__ . '/config/db.php';
$pageTitle = 'All Sales';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $stmt = $pdo->prepare("DELETE FROM sales WHERE id = ?");
    $stmt->execute([$_POST['delete_id']]);
    header('Location: /sales.php');
    exit;
}

$typeFilter = $_GET['type'] ?? '';
$search = trim($_GET['q'] ?? '');

$where = [];
$params = [];

if ($typeFilter !== '' && in_array($typeFilter, $PROPERTY_TYPES, true)) {
    $where[] = 'property_type = ?';
    $params[] = $typeFilter;
}
if ($search !== '') {
    $where[] = '(buyer_name LIKE ? OR property_name LIKE ? OR agent_name LIKE ?)';
    $like = '%' . $search . '%';
    array_push($params, $like, $like, $like);
}

$sql = 'SELECT * FROM sales';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= " ORDER BY date_sold DESC, id DESC";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$sales = $stmt->fetchAll();

// Lightweight dataset (honors the type filter, not the text search) that
// powers the live suggestion dropdown under the search box.
$suggestSql = 'SELECT property_name, buyer_name, agent_name FROM sales';
$suggestParams = [];
if ($typeFilter !== '' && in_array($typeFilter, $PROPERTY_TYPES, true)) {
    $suggestSql .= ' WHERE property_type = ?';
    $suggestParams[] = $typeFilter;
}
$stmt = $pdo->prepare($suggestSql);
$stmt->execute($suggestParams);
$suggestData = $stmt->fetchAll(PDO::FETCH_ASSOC);

function peso($n) { return '₱' . number_format((float)$n, 0); }

require __DIR__ . '/includes/header.php';
?>
<div class="page-header">
  <div>
    <h1>All Sales</h1>
    <p>Every sold property on record.</p>
  </div>
  <a href="/add_sale.php" class="btn btn-primary">+ Add Sold Property</a>
</div>

<form method="get" class="filter-row" id="searchForm" autocomplete="off">
  <div class="search-wrap">
    <input type="search" name="q" id="searchInput" class="search-input" placeholder="Search buyer, property, or agent…" value="<?= htmlspecialchars($search) ?>">
    <div id="searchSuggestions" class="search-suggestions"></div>
  </div>
  <select name="type" onchange="this.form.submit()">
    <option value="">All property types</option>
    <?php foreach ($PROPERTY_TYPES as $t): ?>
      <option value="<?= htmlspecialchars($t) ?>" <?= $typeFilter === $t ? 'selected' : '' ?>><?= htmlspecialchars($t) ?></option>
    <?php endforeach; ?>
  </select>
  <button type="submit" class="btn btn-ghost">Search</button>
  <?php if ($search !== '' || $typeFilter !== ''): ?>
    <a href="/sales.php" class="btn btn-ghost">Clear</a>
  <?php endif; ?>
  <span class="badge"><?= count($sales) ?> record<?= count($sales) === 1 ? '' : 's' ?></span>
</form>

<div class="card">
  <table>
    <thead>
      <tr>
        <th>Property</th><th>Type</th><th>Buyer</th><th>Agent</th><th>Date Sold</th><th class="num">Price</th><th></th>
      </tr>
    </thead>
    <tbody>
      <?php if (!$sales): ?>
        <tr><td colspan="7" style="color:var(--text-muted)">No sales found.</td></tr>
      <?php endif; ?>
      <?php foreach ($sales as $s): ?>
      <tr>
        <td>
          <?= htmlspecialchars($s['property_name']) ?>
          <?php if ($s['location']): ?><div style="color:var(--text-muted);font-size:12px;"><?= htmlspecialchars($s['location']) ?></div><?php endif; ?>
        </td>
        <td><span class="badge"><?= htmlspecialchars($s['property_type']) ?></span></td>
        <td>
          <?= htmlspecialchars($s['buyer_name']) ?>
          <?php if ($s['buyer_contact']): ?><div style="color:var(--text-muted);font-size:12px;"><?= htmlspecialchars($s['buyer_contact']) ?></div><?php endif; ?>
        </td>
        <td><?= htmlspecialchars($s['agent_name']) ?></td>
        <td><?= date('M j, Y', strtotime($s['date_sold'])) ?></td>
        <td class="num"><?= peso($s['price']) ?></td>
        <td>
          <form method="post" onsubmit="return confirm('Delete this sale record?');">
            <input type="hidden" name="delete_id" value="<?= (int)$s['id'] ?>">
            <button type="submit" class="btn btn-danger">Delete</button>
          </form>
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<script>
const suggestData = <?= json_encode($suggestData) ?>;
const searchInput = document.getElementById('searchInput');
const suggestBox = document.getElementById('searchSuggestions');
const searchForm = document.getElementById('searchForm');

function renderSuggestions(term) {
  const q = term.trim().toLowerCase();
  if (!q) { suggestBox.classList.remove('open'); suggestBox.innerHTML = ''; return; }

  const seen = new Set();
  const matches = [];
  for (const row of suggestData) {
    const hitProperty = row.property_name.toLowerCase().includes(q);
    const hitBuyer = row.buyer_name.toLowerCase().includes(q);
    const hitAgent = row.agent_name.toLowerCase().includes(q);
    if (!hitProperty && !hitBuyer && !hitAgent) continue;

    const key = row.property_name + '|' + row.buyer_name + '|' + row.agent_name;
    if (seen.has(key)) continue;
    seen.add(key);

    matches.push({
      value: hitProperty ? row.property_name : (hitBuyer ? row.buyer_name : row.agent_name),
      property: row.property_name,
      buyer: row.buyer_name,
      agent: row.agent_name
    });
    if (matches.length >= 8) break;
  }

  if (!matches.length) {
    suggestBox.innerHTML = '<div class="search-suggestions__empty">No matches</div>';
    suggestBox.classList.add('open');
    return;
  }

  suggestBox.innerHTML = matches.map(m => `
    <button type="button" class="search-suggestions__item" data-value="${m.value.replace(/"/g, '&quot;')}">
      <span class="search-suggestions__title">${m.property}</span>
      <span class="search-suggestions__meta">${m.buyer} &middot; ${m.agent}</span>
    </button>
  `).join('');
  suggestBox.classList.add('open');
}

searchInput.addEventListener('input', () => renderSuggestions(searchInput.value));
searchInput.addEventListener('focus', () => renderSuggestions(searchInput.value));

suggestBox.addEventListener('click', (e) => {
  const item = e.target.closest('.search-suggestions__item');
  if (!item) return;
  searchInput.value = item.dataset.value;
  suggestBox.classList.remove('open');
  searchForm.submit();
});

document.addEventListener('click', (e) => {
  if (!e.target.closest('.search-wrap')) {
    suggestBox.classList.remove('open');
  }
});
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
