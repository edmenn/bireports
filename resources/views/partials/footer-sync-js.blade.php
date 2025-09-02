<script>
// Ajusta los colores del footer para igualar al navbar (claro/oscuro)
(function(){
    function syncFooter(){
        var nav = document.querySelector('.main-header.navbar');
        var footer = document.querySelector('.main-footer');
        if(!nav || !footer) return;
        var cs = window.getComputedStyle(nav);
        footer.style.backgroundColor = cs.backgroundColor || '';
        footer.style.color = cs.color || '';
    }
    document.addEventListener('DOMContentLoaded', function(){
        syncFooter();
        var nav = document.querySelector('.main-header.navbar');
        if (nav && window.MutationObserver) {
            var obs = new MutationObserver(syncFooter);
            obs.observe(nav, { attributes: true, attributeFilter: ['class','style'] });
        }
    }, {once:true});
})();
</script>
