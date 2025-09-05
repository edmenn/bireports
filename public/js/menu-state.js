(() => {
  const key = 'app:sidebar:openIdxs';
  const sidebar = document.querySelector('.main-sidebar, .nav-sidebar');
  if (!sidebar) return;

  const items = Array.from(document.querySelectorAll('.nav-sidebar > li.nav-item.has-treeview'));
  if (!items.length) return;

  // Restore previously open groups
  try {
    const saved = JSON.parse(localStorage.getItem(key) || '[]');
    saved.forEach((idx) => {
      if (items[idx]) items[idx].classList.add('menu-open');
    });
  } catch (_) {}

  const save = () => {
    try {
      const openIdxs = items.reduce((acc, li, i) => {
        if (li.classList.contains('menu-open')) acc.push(i);
        return acc;
      }, []);
      localStorage.setItem(key, JSON.stringify(openIdxs));
    } catch (_) {}
  };

  // Observe class changes to persist state
  const mo = new MutationObserver(() => save());
  items.forEach((li) => mo.observe(li, { attributes: true, attributeFilter: ['class'] }));

  // Persist when navigating (safety)
  window.addEventListener('beforeunload', save);
})();

