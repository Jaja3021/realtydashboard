<?php
require_once __DIR__ . '/config/db.php';
$pageTitle = 'Add Sold Property';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $property_name = trim($_POST['property_name'] ?? '');
    $property_type = trim($_POST['property_type'] ?? '');
    $location      = trim($_POST['location'] ?? '');
    $buyer_name    = trim($_POST['buyer_name'] ?? '');
    $buyer_contact = trim($_POST['buyer_contact'] ?? '');
    $price         = $_POST['price'] ?? '';
    $agent_name    = trim($_POST['agent_name'] ?? '');
    $date_sold     = $_POST['date_sold'] ?? '';

    if ($property_name === '') $errors[] = 'Property name is required.';
    if (!in_array($property_type, $PROPERTY_TYPES, true)) $errors[] = 'Select a valid property type.';
    if ($buyer_name === '') $errors[] = "Buyer's full name is required.";
    if ($agent_name === '') $errors[] = 'Sales agent is required.';
    if (!is_numeric($price) || (float)$price <= 0) $errors[] = 'Enter a valid selling price.';
    if (!$date_sold || !DateTime::createFromFormat('Y-m-d', $date_sold)) $errors[] = 'Select a valid date sold.';

    if (!$errors) {
        $stmt = $pdo->prepare("INSERT INTO sales (property_name, property_type, location, buyer_name, buyer_contact, price, agent_name, date_sold)
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$property_name, $property_type, $location, $buyer_name, $buyer_contact, $price, $agent_name, $date_sold]);
        $success = true;
    }
}

require __DIR__ . '/includes/header.php';
?>
<div class="page-header">
  <div>
    <h1>Add Sold Property</h1>
    <p>Log the details of a property that was just sold (nabenta).</p>
  </div>
</div>

<?php if ($success): ?>
  <div class="alert alert-success">Sale recorded successfully. <a href="/sales.php">View all sales</a> or add another below.</div>
<?php endif; ?>

<?php if ($errors): ?>
  <div class="alert" style="color: var(--status-critical);">
    <?php foreach ($errors as $e): ?><div><?= htmlspecialchars($e) ?></div><?php endforeach; ?>
  </div>
<?php endif; ?>

<div class="card">
  <form method="post" class="form-grid" novalidate>
    <div class="field">
      <label for="property_name">Property Name / Unit</label>
      <input type="text" id="property_name" name="property_name" placeholder="e.g. Sunrise Villas Blk 3 Lot 12" value="<?= htmlspecialchars($_POST['property_name'] ?? '') ?>" required>
    </div>

    <div class="field">
      <label for="property_type">Property Type</label>
      <select id="property_type" name="property_type" required>
        <option value="">Select type</option>
        <?php foreach ($PROPERTY_TYPES as $t): ?>
          <option value="<?= htmlspecialchars($t) ?>" <?= (($_POST['property_type'] ?? '') === $t) ? 'selected' : '' ?>><?= htmlspecialchars($t) ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <div class="field">
      <label for="location">Location</label>
      <input type="text" id="location" name="location" placeholder="e.g. Cavite" value="<?= htmlspecialchars($_POST['location'] ?? '') ?>">
    </div>

    <div class="field">
      <label for="price">Selling Price (₱)</label>
      <input type="number" id="price" name="price" min="0" step="0.01" placeholder="e.g. 4500000" value="<?= htmlspecialchars($_POST['price'] ?? '') ?>" required>
    </div>

    <div class="field">
      <label for="buyer_name">Buyer's Full Name</label>
      <input type="text" id="buyer_name" name="buyer_name" placeholder="e.g. Maria Santos Dela Cruz" value="<?= htmlspecialchars($_POST['buyer_name'] ?? '') ?>" required>
    </div>

    <div class="field">
      <label for="buyer_contact">Buyer Contact</label>
      <input type="text" id="buyer_contact" name="buyer_contact" placeholder="e.g. 0917-111-2222" value="<?= htmlspecialchars($_POST['buyer_contact'] ?? '') ?>">
    </div>

    <div class="field">
      <label for="agent_name">Sales Agent</label>
      <input type="text" id="agent_name" name="agent_name" placeholder="e.g. Jenny Cruz" value="<?= htmlspecialchars($_POST['agent_name'] ?? '') ?>" required>
    </div>

    <div class="field">
      <label for="date_sold">Date Sold</label>
      <input type="date" id="date_sold" name="date_sold" value="<?= htmlspecialchars($_POST['date_sold'] ?? date('Y-m-d')) ?>" required>
    </div>

    <div class="form-actions">
      <button type="submit" class="btn btn-primary">Save Sale</button>
      <a href="/sales.php" class="btn btn-ghost">View All Sales</a>
    </div>
  </form>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
