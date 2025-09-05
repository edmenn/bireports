@php
    // Espera: $sections (array de secciones con items), $current (array de nombres seleccionados)
    $current = $current ?? [];
    $disableInputs = $disableInputs ?? false; // cuando true, deshabilita los checkboxes (ej: admin)
@endphp

@foreach($sections as $section)
    <x-adminlte-card title="{{ $section['label'] }}" icon="fas fa-list text-lightblue" theme="light" class="mb-2">
        <div class="mb-2">
            <button type="button" class="btn btn-sm btn-outline-secondary js-toggle-section" data-target="sec-{{ $section['key'] }}" data-mode="all" @if($disableInputs) disabled @endif>
                Seleccionar todos
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary js-toggle-section" data-target="sec-{{ $section['key'] }}" data-mode="none" @if($disableInputs) disabled @endif>
                Quitar todos
            </button>
        </div>
        <div id="sec-{{ $section['key'] }}" class="row">
            @foreach($section['items'] as $perm)
                <div class="col-md-4 mb-2">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox" class="custom-control-input" id="perm_{{ md5($perm['name']) }}"
                               name="permissions[]" value="{{ $perm['name'] }}"
                               @checked(in_array($perm['name'], $current, true)) @if($disableInputs) disabled @endif>
                        <label class="custom-control-label" for="perm_{{ md5($perm['name']) }}">
                            {{ $perm['label'] }}
                            <small class="text-muted d-block">{{ $perm['name'] }}</small>
                        </label>
                    </div>
                </div>
            @endforeach
        </div>
    </x-adminlte-card>
@endforeach

@push('js')
<script>
(function(){
  document.querySelectorAll('.js-toggle-section').forEach(btn => {
    btn.addEventListener('click', function(){
      const target = this.getAttribute('data-target');
      const mode = this.getAttribute('data-mode');
      const cont = document.getElementById(target);
      if (!cont) return;
      cont.querySelectorAll('input[type="checkbox"]').forEach(chk => chk.checked = (mode === 'all'));
    });
  });
})();
</script>
@endpush
