(() => {
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
})();
