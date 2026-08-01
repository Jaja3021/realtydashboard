<?php if (!isset($pdo)) { require_once __DIR__ . '/../config/db.php'; } ?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? htmlspecialchars($pageTitle) . ' · Realty Dashboard' : 'Realty Dashboard' ?></title>
<link rel="stylesheet" href="/assets/css/style.css">
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js"></script>
</head>
<body>
<div class="layout">
<?php require __DIR__ . '/sidebar.php'; ?>
<main class="main">
