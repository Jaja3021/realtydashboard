(function () {
  const links = [
    { href: '/', label: 'Dashboard', icon: '<rect x="3" y="3" width="7" height="9" rx="1.5"/><rect x="14" y="3" width="7" height="5" rx="1.5"/><rect x="14" y="12" width="7" height="9" rx="1.5"/><rect x="3" y="16" width="7" height="5" rx="1.5"/>' },
    { href: '/add-sale', label: 'Add Sold Property', icon: '<path d="M12 5v14M5 12h14"/>' },
    { href: '/sales', label: 'All Sales', icon: '<path d="M4 6h16M4 12h16M4 18h10"/>' },
    { href: '/top-sales', label: 'Top Sales', icon: '<path d="M8 21h8M12 17v4M7 4h10l-1 8.5a4 4 0 0 1-8 0L7 4Z"/><path d="M5 6H3.5A1.5 1.5 0 0 0 2 7.5v0A3.5 3.5 0 0 0 5.5 11H7M19 6h1.5A1.5 1.5 0 0 1 22 7.5v0a3.5 3.5 0 0 1-3.5 3.5H17"/>' },
    { href: '/calendar', label: 'Calendar', icon: '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>' },
  ];

  const current = window.location.pathname.replace(/\/$/, '') || '/';

  const navHtml = links.map((l) => {
    const isActive = (l.href === '/' && current === '/') || (l.href !== '/' && current === l.href);
    return `
      <a class="sidebar__link ${isActive ? 'active' : ''}" href="${l.href}">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">${l.icon}</svg>
        ${l.label}
      </a>`;
  }).join('');

  const html = `
    <button type="button" id="sidebarToggle" class="sidebar-toggle" aria-label="Open menu" aria-expanded="false" aria-controls="sidebar">
      <svg id="sidebarToggleIcon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
    </button>
    <div id="sidebarBackdrop" class="sidebar-backdrop"></div>
    <aside class="sidebar" id="sidebar">
      <div class="sidebar__brand">
        <span class="sidebar__brand-mark"></span>
        Realty Dashboard
      </div>
      <nav class="sidebar__nav">${navHtml}</nav>
      <div class="sidebar__footer">&copy; ${new Date().getFullYear()} Realty Dashboard</div>
    </aside>`;

  document.write(html);

  window.addEventListener('DOMContentLoaded', function () {
    const sidebar = document.getElementById('sidebar');
    const backdrop = document.getElementById('sidebarBackdrop');
    const toggle = document.getElementById('sidebarToggle');
    const toggleIcon = document.getElementById('sidebarToggleIcon');
    const menuIcon = '<path d="M4 6h16M4 12h16M4 18h16"/>';
    const closeIcon = '<path d="M6 6l12 12M18 6L6 18"/>';

    function setOpen(open) {
      sidebar.classList.toggle('open', open);
      backdrop.classList.toggle('open', open);
      toggle.setAttribute('aria-expanded', String(open));
      toggle.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
      toggleIcon.innerHTML = open ? closeIcon : menuIcon;
    }

    toggle.addEventListener('click', () => setOpen(!sidebar.classList.contains('open')));
    backdrop.addEventListener('click', () => setOpen(false));
  });
})();
