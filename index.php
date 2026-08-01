<?php
require_once __DIR__ . '/config/db.php';
$pageTitle = 'Dashboard';

$thisMonthStart = date('Y-m-01');
$nextMonthStart = date('Y-m-01', strtotime('+1 month'));

// Stat: this month
$stmt = $pdo->prepare("SELECT COUNT(*) units, COALESCE(SUM(price),0) revenue FROM sales WHERE date_sold >= ? AND date_sold < ?");
$stmt->execute([$thisMonthStart, $nextMonthStart]);
$monthStat = $stmt->fetch();

// Stat: all time
$allStat = $pdo->query("SELECT COUNT(*) units, COALESCE(SUM(price),0) revenue FROM sales")->fetch();

// Stat: top agent this month
$stmt = $pdo->prepare("SELECT agent_name, COUNT(*) units, SUM(price) revenue FROM sales WHERE date_sold >= ? AND date_sold < ? GROUP BY agent_name ORDER BY revenue DESC LIMIT 1");
$stmt->execute([$thisMonthStart, $nextMonthStart]);
$topAgent = $stmt->fetch();

// Trend: last 6 months revenue
$months = [];
for ($i = 5; $i >= 0; $i--) {
    $key = date('Y-m', strtotime("-$i month"));
    $months[$key] = ['label' => date('M Y', strtotime("-$i month")), 'revenue' => 0, 'units' => 0];
}
$stmt = $pdo->prepare("SELECT DATE_FORMAT(date_sold, '%Y-%m') ym, COUNT(*) units, SUM(price) revenue FROM sales WHERE date_sold >= ? GROUP BY ym");
$stmt->execute([date('Y-m-01', strtotime('-5 month'))]);
foreach ($stmt->fetchAll() as $row) {
    if (isset($months[$row['ym']])) {
        $months[$row['ym']]['revenue'] = (float) $row['revenue'];
        $months[$row['ym']]['units'] = (int) $row['units'];
    }
}
$trendLabels = array_column($months, 'label');
$trendRevenue = array_column($months, 'revenue');

// Breakdown by property type (all time)
$typeRows = $pdo->query("SELECT property_type, COUNT(*) units, SUM(price) revenue FROM sales GROUP BY property_type ORDER BY revenue DESC")->fetchAll();
$typeLabels = array_column($typeRows, 'property_type');
$typeRevenue = array_map('floatval', array_column($typeRows, 'revenue'));

// Recent sales
$recent = $pdo->query("SELECT * FROM sales ORDER BY date_sold DESC, id DESC LIMIT 6")->fetchAll();

function peso($n) { return '₱' . number_format((float)$n, 0); }

require __DIR__ . '/includes/header.php';
?>
<div class="page-header">
  <div>
    <h1>Dashboard</h1>
    <p>Overview of sold properties and sales performance.</p>
  </div>
</div>

<div class="stat-row">
  <div class="stat-tile">
    <div class="stat-tile__label">Units Sold This Month</div>
    <div class="stat-tile__value"><?= (int)$monthStat['units'] ?></div>
    <div class="stat-tile__delta"><?= date('F Y') ?></div>
  </div>
  <div class="stat-tile">
    <div class="stat-tile__label">Revenue This Month</div>
    <div class="stat-tile__value"><?= peso($monthStat['revenue']) ?></div>
    <div class="stat-tile__delta"><?= date('F Y') ?></div>
  </div>
  <div class="stat-tile">
    <div class="stat-tile__label">Top Agent This Month</div>
    <div class="stat-tile__value" style="font-size:20px;"><?= $topAgent ? htmlspecialchars($topAgent['agent_name']) : '—' ?></div>
    <div class="stat-tile__delta good"><?= $topAgent ? peso($topAgent['revenue']) . ' · ' . (int)$topAgent['units'] . ' units' : 'No sales yet' ?></div>
  </div>
  <div class="stat-tile">
    <div class="stat-tile__label">All-Time Units / Revenue</div>
    <div class="stat-tile__value"><?= (int)$allStat['units'] ?></div>
    <div class="stat-tile__delta"><?= peso($allStat['revenue']) ?></div>
  </div>
</div>

<div class="chart-grid">
  <div class="card">
    <h2>Revenue trend <span class="muted">· last 6 months</span></h2>
    <div class="chart-wrap"><canvas id="trendChart"></canvas></div>
  </div>
  <div class="card">
    <h2>Sales by property type <span class="muted">· all time</span></h2>
    <div class="chart-wrap"><canvas id="typeChart"></canvas></div>
  </div>
</div>

<div class="card">
  <h2>Recently sold</h2>
  <table>
    <thead>
      <tr><th>Property</th><th>Type</th><th>Agent</th><th>Date Sold</th><th class="num">Price</th></tr>
    </thead>
    <tbody>
      <?php if (!$recent): ?>
        <tr><td colspan="5" style="color:var(--text-muted)">No sales recorded yet. <a href="/add_sale.php">Add one</a>.</td></tr>
      <?php endif; ?>
      <?php foreach ($recent as $r): ?>
      <tr>
        <td><?= htmlspecialchars($r['property_name']) ?></td>
        <td><span class="badge"><?= htmlspecialchars($r['property_type']) ?></span></td>
        <td><?= htmlspecialchars($r['agent_name']) ?></td>
        <td><?= date('M j, Y', strtotime($r['date_sold'])) ?></td>
        <td class="num"><?= peso($r['price']) ?></td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<script>
const css = getComputedStyle(document.documentElement);
const seriesColor = css.getPropertyValue('--series-1').trim();
const gridColor = css.getPropertyValue('--gridline').trim();
const textMuted = css.getPropertyValue('--text-muted').trim();
const textSecondary = css.getPropertyValue('--text-secondary').trim();

Chart.defaults.font.family = "system-ui, -apple-system, 'Segoe UI', sans-serif";
Chart.defaults.font.size = 12;
Chart.defaults.color = textMuted;

new Chart(document.getElementById('trendChart'), {
  type: 'bar',
  data: {
    labels: <?= json_encode($trendLabels) ?>,
    datasets: [{
      label: 'Revenue',
      data: <?= json_encode($trendRevenue) ?>,
      backgroundColor: seriesColor,
      borderRadius: 4,
      maxBarThickness: 40
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: (ctx) => ' ₱' + ctx.parsed.y.toLocaleString()
        }
      }
    },
    scales: {
      x: { grid: { display: false }, ticks: { color: textSecondary } },
      y: {
        grid: { color: gridColor },
        border: { display: false },
        ticks: {
          callback: (v) => '₱' + (v >= 1000000 ? (v/1000000) + 'M' : v >= 1000 ? (v/1000) + 'k' : v)
        }
      }
    }
  }
});

new Chart(document.getElementById('typeChart'), {
  type: 'bar',
  data: {
    labels: <?= json_encode($typeLabels) ?>,
    datasets: [{
      label: 'Revenue',
      data: <?= json_encode($typeRevenue) ?>,
      backgroundColor: seriesColor,
      borderRadius: 4,
      maxBarThickness: 22
    }]
  },
  options: {
    indexAxis: 'y',
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: {
        callbacks: {
          label: (ctx) => ' ₱' + ctx.parsed.x.toLocaleString()
        }
      }
    },
    scales: {
      x: {
        grid: { color: gridColor },
        border: { display: false },
        ticks: {
          callback: (v) => '₱' + (v >= 1000000 ? (v/1000000) + 'M' : v >= 1000 ? (v/1000) + 'k' : v)
        }
      },
      y: { grid: { display: false }, ticks: { color: textSecondary } }
    }
  }
});
</script>
<?php require __DIR__ . '/includes/footer.php'; ?>
