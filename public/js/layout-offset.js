// Sync CSS var --app-navbar-h with real .main-header height
(function(){
  function setNavH(){
    var header = document.querySelector('.main-header');
    var h = header ? Math.ceil(header.getBoundingClientRect().height) : 56;
    document.documentElement.style.setProperty('--app-navbar-h', h + 'px');
  }
  var ro;
  if ('ResizeObserver' in window) {
    var header = document.querySelector('.main-header');
    if (header) {
      ro = new ResizeObserver(setNavH);
      ro.observe(header);
    }
  }
  window.addEventListener('load', setNavH, { once: true });
  window.addEventListener('resize', setNavH);
  document.addEventListener('transitionend', function(e){
    if (e.target && e.target.classList && e.target.classList.contains('main-header')) setNavH();
  });
})();

