@php
    /** @var iterable<array<string, mixed>> $sections */
    $sections = is_iterable($sections ?? null) ? $sections : [];
    $current = is_array($current ?? null) ? $current : [];
    $disableInputs = (bool) ($disableInputs ?? false);

    $extractScalar = static function ($value) use (&$extractScalar): string {
        if ($value instanceof \Illuminate\Support\Collection) {
            return $extractScalar($value->all());
        }

        if ($value instanceof \Stringable) {
            return (string) $value;
        }

        if (is_array($value)) {
            foreach (['label', 'title', 'name', 'text'] as $preferredKey) {
                if (array_key_exists($preferredKey, $value) && (is_scalar($value[$preferredKey]) || $value[$preferredKey] === null)) {
                    return (string) $value[$preferredKey];
                }
            }

            foreach ($value as $item) {
                $candidate = $extractScalar($item);
                if ($candidate !== '') {
                    return $candidate;
                }
            }

            return '';
        }

        return is_scalar($value) || $value === null ? (string) $value : '';
    };

    $slugify = static function (string $value, string $fallback = 'section'): string {
        $slug = preg_replace('/[^A-Za-z0-9_-]+/', '-', $value);
        $slug = trim($slug ?? '', '-');
        return $slug === '' ? $fallback : $slug;
    };
@endphp

@foreach($sections as $index => $section)
    @php
        $sectionKey = $extractScalar($section['key'] ?? '');
        $sectionLabel = $extractScalar($section['label'] ?? '');
        $items = is_iterable($section['items'] ?? []) ? $section['items'] : [];
        $domId = 'sec-' . $slugify($sectionKey !== '' ? $sectionKey : (string) $index, 'sec-' . $index);
    @endphp

    <x-adminlte-card title="{{ $sectionLabel }}" icon="fas fa-list text-lightblue" theme="light" class="mb-2">
        <div class="mb-2">
            <button type="button"
                    class="btn btn-sm btn-outline-secondary js-toggle-section"
                    data-target="{{ $domId }}"
                    data-mode="all"
                    @if($disableInputs) disabled @endif>
                {{ __('roles.permissions.select_all') }}
            </button>
            <button type="button"
                    class="btn btn-sm btn-outline-secondary js-toggle-section"
                    data-target="{{ $domId }}"
                    data-mode="none"
                    @if($disableInputs) disabled @endif>
                {{ __('roles.permissions.deselect_all') }}
            </button>
        </div>

        <div id="{{ $domId }}" class="row">
            @foreach($items as $itemIndex => $perm)
                @php
                    $permName = $extractScalar($perm['name'] ?? '');
                    $permLabel = $extractScalar($perm['label'] ?? $permName);
                    $inputId = 'perm_' . md5($domId . '_' . $itemIndex . '_' . $permName);
                @endphp

                <div class="col-md-4 mb-2">
                    <div class="custom-control custom-checkbox">
                        <input type="checkbox"
                               class="custom-control-input"
                               id="{{ $inputId }}"
                               name="permissions[]"
                               value="{{ $permName }}"
                               @checked($permName !== '' && in_array($permName, $current, true))
                               @if($disableInputs) disabled @endif>
                        <label class="custom-control-label" for="{{ $inputId }}">
                            {{ $permLabel }}
                            @if($permName !== '')
                                <small class="text-muted d-block">{{ $permName }}</small>
                            @endif
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
  document.querySelectorAll('.js-toggle-section').forEach(function(btn){
    btn.addEventListener('click', function(){
      var target = this.getAttribute('data-target');
      var mode = this.getAttribute('data-mode');
      var container = document.getElementById(target);
      if (!container) return;
      container.querySelectorAll('input[type="checkbox"]').forEach(function(chk){
        chk.checked = (mode === 'all');
      });
    });
  });
})();
</script>
@endpush
