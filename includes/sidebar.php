<?php $current = basename($_SERVER['PHP_SELF']); ?>
<aside class="sidebar">
  <div class="sidebar__brand">
    <span class="sidebar__brand-mark"></span>
    Realty Dashboard
  </div>
  <nav class="sidebar__nav">
    <a class="sidebar__link <?= $current === 'index.php' ? 'active' : '' ?>" href="/index.php">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/></svg>
      Dashboard
    </a>
    <a class="sidebar__link <?= $current === 'add_sale.php' ? 'active' : '' ?>" href="/add_sale.php">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
      Add Sold Property
    </a>
    <a class="sidebar__link <?= $current === 'sales.php' ? 'active' : '' ?>" href="/sales.php">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h10"/></svg>
      All Sales
    </a>
    <a class="sidebar__link <?= $current === 'top_sales.php' ? 'active' : '' ?>" href="/top_sales.php">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M8 21h8M12 17v4M7 4h10l-1 8.5a4 4 0 0 1-8 0L7 4Z"/><path d="M5 6H3.5A1.5 1.5 0 0 0 2 7.5v0A3.5 3.5 0 0 0 5.5 11H7M19 6h1.5A1.5 1.5 0 0 1 22 7.5v0a3.5 3.5 0 0 1-3.5 3.5H17"/></svg>
      Top Sales
    </a>
  </nav>
  <div class="sidebar__footer">&copy; <?= date('Y') ?> Realty Dashboard</div>
</aside>
