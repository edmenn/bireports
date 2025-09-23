@once
    @push('js')
        <script>
        (function(){
            document.querySelectorAll('form.js-fav-form').forEach(function(form){
                form.addEventListener('submit', function(e){
                    e.preventDefault();
                    const btn = form.querySelector('.js-fav-btn');
                    const icon = btn?.querySelector('i');
                    if (!btn) { form.submit(); return; }
                    const url = form.getAttribute('action');
                    const token = form.querySelector('input[name=_token]')?.value;
                    fetch(url, {
                            method:'POST',
                            headers:{
                                'X-Requested-With':'XMLHttpRequest',
                                'X-CSRF-TOKEN':token,
                                'Accept':'application/json',
                                'Content-Type':'application/x-www-form-urlencoded; charset=UTF-8'
                            },
                            body:new URLSearchParams({_token:token}),
                            credentials:'same-origin'
                        })
                        .then(function(resp){
                            if(!resp.ok){ throw new Error('HTTP '+resp.status); }
                            return resp.json().catch(function(){ return {}; });
                        })
                        .then(function(){
                            btn.classList.toggle('is-on');
                            if (icon){
                                if (icon.classList.contains('fas')){
                                    icon.classList.remove('fas'); icon.classList.add('far');
                                } else {
                                    icon.classList.remove('far'); icon.classList.add('fas');
                                }
                            }
                        })
                        .catch(function(){ form.submit(); });
                }, {passive:false});
            });
        })();
        </script>
    @endpush
@endonce
