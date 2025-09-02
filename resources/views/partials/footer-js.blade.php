<script>
// Añade un footer corporativo fijo al final del body
(function(){
    try{
        var el = document.createElement('div');
        el.className = 'app-corporate-footer';
        el.textContent = @json($text);
        function syncColors(){
            var nav = document.querySelector('.main-header.navbar');
            if(!nav) return;
            var cs = window.getComputedStyle(nav);
            el.style.backgroundColor = cs.backgroundColor || '';
            el.style.color = cs.color || '';
        }
        document.addEventListener('DOMContentLoaded', function(){
            document.body.appendChild(el);
            syncColors();
            // Observar cambios de clase en el header (p.ej. dark/light)
            var nav = document.querySelector('.main-header.navbar');
            if (nav && window.MutationObserver) {
                var obs = new MutationObserver(syncColors);
                obs.observe(nav, { attributes: true, attributeFilter: ['class','style'] });
            }
        }, {once:true});
    }catch(e){ /* noop */ }
})();
</script>
