@php
    /** @var iterable<array<string, mixed>> $sections */
    $sections = is_iterable($sections ?? null) ? $sections : [];
    $current = is_array($current ?? null) ? $current : [];
    $disableInputs = (bool) ($disableInputs ?? false);

    $extractScalar = static function ($value) use (&$extractScalar): string {
        if ($value instanceof \Illuminate\Support\Collection) return $extractScalar($value->all());
        if ($value instanceof \Stringable) return (string) $value;
        if (is_array($value)) {
            foreach (['label', 'title', 'name', 'text'] as $preferredKey) {
                if (array_key_exists($preferredKey, $value) && (is_scalar($value[$preferredKey]) || $value[$preferredKey] === null)) {
                    return (string) $value[$preferredKey];
                }
            }
            foreach ($value as $item) {
                $candidate = $extractScalar($item);
                if ($candidate !== '') return $candidate;
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
        if (empty($items)) continue;
        $domId = 'sec-' . $slugify($sectionKey !== '' ? $sectionKey : (string) $index, 'sec-' . $index);
    @endphp

    <x-adminlte-card theme="light" theme-mode="outline" class="mb-3">
        {{-- Header: title left, buttons right --}}
        <div class="d-flex justify-content-between align-items-center flex-wrap mb-3 px-1">
            <h5 class="m-0 d-flex align-items-center text-primary">
                <i class="fas fa-list text-lightblue mr-2"></i> {{ $sectionLabel }}
            </h5>
            <div class="d-flex align-items-center gap-2">
                <button type="button"
                        class="btn btn-outline-secondary btn-xs js-toggle-section"
                        data-target="{{ $domId }}"
                        data-mode="all"
                        @if($disableInputs) disabled @endif>
                    <i class="fas fa-check-double mr-1"></i>{{ __('roles.permissions.select_all') }}
                </button>
                <button type="button"
                        class="btn btn-outline-secondary btn-xs js-toggle-section"
                        data-target="{{ $domId }}"
                        data-mode="none"
                        @if($disableInputs) disabled @endif>
                    <i class="fas fa-times mr-1"></i>{{ __('roles.permissions.deselect_all') }}
                </button>
            </div>
        </div>

        {{-- Permission checkboxes --}}
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

@push('css')
<style>
/* === Section header (title + actions inline) === */
.card .d-flex.justify-content-between {
    gap: .75rem;
}

.card .btn-xs {
    height: 28px !important;
    padding: 0 .6rem !important;
    font-size: .75rem !important;
    line-height: 1 !important;
    border-radius: 4px !important;
    display: inline-flex !important;
    align-items: center !important;
    justify-content: center !important;
    font-weight: 500 !important;
}
.card .btn-xs i {
    font-size: .7rem !important;
    margin-right: .3rem !important;
}

/* === Permission list === */
.custom-control { margin-bottom: .4rem; }
.custom-control-label { font-size: .9rem; font-weight: 600; }
.custom-control-label small { font-weight: 400; }
</style>
@endpush

@push('js')
<script>
(() => {
  document.querySelectorAll('.js-toggle-section').forEach(btn => {
    btn.addEventListener('click', () => {
      const target = btn.getAttribute('data-target');
      const mode = btn.getAttribute('data-mode');
      const container = document.getElementById(target);
      if (!container) return;
      container.querySelectorAll('input[type="checkbox"]').forEach(chk => {
        chk.checked = (mode === 'all');
      });
    });
  });
})();
</script>
@endpush
