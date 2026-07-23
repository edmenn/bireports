(() => {
  const init = () => {
  const body = document.body;
  const sizeToggle = document.getElementById('sidebar-size-toggle');
  const compactKey = 'app:sidebar:compact';
  const setCompact = (compact) => {
    body.classList.toggle('sidebar-compact', compact);
    sizeToggle?.setAttribute('aria-pressed', compact ? 'true' : 'false');
  };

  try { setCompact(localStorage.getItem(compactKey) === '1'); } catch (_) {}
  sizeToggle?.addEventListener('click', () => {
    const compact = !body.classList.contains('sidebar-compact');
    setCompact(compact);
    try { localStorage.setItem(compactKey, compact ? '1' : '0'); } catch (_) {}
  });

  const key = 'app:sidebar:openGroups';
  const groups = Array.from(document.querySelectorAll('#app-sidebar-menu .collapse[id]'));
  if (!groups.length || typeof bootstrap === 'undefined') return;

  let saved = [];
  try { saved = JSON.parse(localStorage.getItem(key) || '[]'); } catch (_) {}

  groups.forEach((group) => {
    if (saved.includes(group.id)) bootstrap.Collapse.getOrCreateInstance(group, { toggle: false }).show();
    group.addEventListener('shown.bs.collapse', save);
    group.addEventListener('hidden.bs.collapse', save);
  });

  function save() {
    try {
      localStorage.setItem(key, JSON.stringify(groups.filter((group) => group.classList.contains('show')).map((group) => group.id)));
    } catch (_) {}
  }
  };

  if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', init, { once: true });
  else init();
})();
