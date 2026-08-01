<?php
require_once __DIR__ . '/config/db.php';
$pageTitle = 'Top Sales';

$availableMonths = $pdo->query("SELECT DISTINCT DATE_FORMAT(date_sold, '%Y-%m') ym FROM sales ORDER BY ym DESC")->fetchAll(PDO::FETCH_COLUMN);

$selectedMonth = $_GET['month'] ?? ($availableMonths[0] ?? date('Y-m'));
if (!preg_match('/^\d{4}-\d{2}$/', $selectedMonth)) {
    $selectedMonth = date('Y-m');
}
$monthStart = $selectedMonth . '-01';
$monthEnd = date('Y-m-01', strtotime($monthStart . ' +1 month'));

$stmt = $pdo->prepare("SELECT agent_name, COUNT(*) units, SUM(price) revenue
                        FROM sales
                        WHERE date_sold >= ? AND date_sold < ?
                        GROUP BY agent_name
                        ORDER BY revenue DESC");
$stmt->execute([$monthStart, $monthEnd]);
$leaderboard = $stmt->fetchAll();

$monthTotal = array_sum(array_column($leaderboard, 'revenue'));

function peso($n) { return '₱' . number_format((float)$n, 0); }

require __DIR__ . '/includes/header.php';
?>
<div class="page-header">
  <div>
    <h1>Top Sales</h1>
    <p>Sales agent leaderboard for the selected month.</p>
  </div>
</div>

<form method="get" class="filter-row">
  <select name="month" onchange="this.form.submit()">
    <?php if (!$availableMonths): ?>
      <option value="<?= $selectedMonth ?>"><?= date('F Y', strtotime($monthStart)) ?></option>
    <?php endif; ?>
    <?php foreach ($availableMonths as $ym): ?>
      <option value="<?= $ym ?>" <?= $ym === $selectedMonth ? 'selected' : '' ?>><?= date('F Y', strtotime($ym . '-01')) ?></option>
    <?php endforeach; ?>
  </select>
  <span class="badge"><?= count($leaderboard) ?> agent<?= count($leaderboard) === 1 ? '' : 's' ?> · <?= peso($monthTotal) ?> total</span>
</form>

<div class="chart-grid" style="grid-template-columns: 1fr 1.1fr;">
  <div class="card">
    <h2>Revenue by agent <span class="muted">· <?= date('F Y', strtotime($monthStart)) ?></span></h2>
    <div class="chart-wrap" style="height: <?= max(180, count($leaderboard) * 44) ?>px;"><canvas id="agentChart"></canvas></div>
  </div>

  <div class="card">
    <h2>Leaderboard</h2>
    <table>
      <thead>
        <tr><th>#</th><th>Agent</th><th class="num">Units</th><th class="num">Revenue</th></tr>
      </thead>
      <tbody>
        <?php if (!$leaderboard): ?>
          <tr><td colspan="4" style="color:var(--text-muted)">No sales recorded for this month.</td></tr>
        <?php endif; ?>
        <?php foreach ($leaderboard as $i => $row): ?>
        <tr>
          <td><span class="rank <?= $i === 0 ? 'rank-1' : '' ?>"><?= $i + 1 ?></span></td>
          <td><?= htmlspecialchars($row['agent_name']) ?></td>
          <td class="num"><?= (int)$row['units'] ?></td>
          <td class="num"><?= peso($row['revenue']) ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

<script>
const css = getComputedStyle(document.documentElement);
const seriesColor = css.getPropertyValue('--series-1').trim();
const gridColor = css.getPropertyValue('--gridline').trim();
const textSecondary = css.getPropertyValue('--text-secondary').trim();

Chart.defaults.font.family = "system-ui, -apple-system, 'Segoe UI', sans-serif";
Chart.defaults.font.size = 12;
Chart.defaults.color = textSecondary;

new Chart(document.getElementById('agentChart'), {
  type: 'bar',
  data: {
    labels: <?= json_encode(array_column($leaderboard, 'agent_name')) ?>,
    datasets: [{
      label: 'Revenue',
      data: <?= json_encode(array_map('floatval', array_column($leaderboard, 'revenue'))) ?>,
      backgroundColor: seriesColor,
      borderRadius: 4,
      maxBarThickness: 26
    }]
  },
  options: {
    indexAxis: 'y',
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: { callbacks: { label: (ctx) => ' ₱' + ctx.parsed.x.toLocaleString() } }
    },
    scales: {
      x: {
        grid: { color: gridColor },
        border: { display: false },
        ticks: { callback: (v) => '₱' + (v >= 1000000 ? (v/1000000) + 'M' : v >= 1000 ? (v/1000) + 'k' : v) }
      },
      y: { grid: { display: false } }
    }
  }
});
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
