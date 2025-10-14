@props([
    'title',
    'subtitle' => null,
    'icon' => null,
])

<div {{ $attributes->class(['page-header d-flex flex-wrap gap-3 align-items-start align-items-md-center justify-content-between']) }}>
    <div class="d-flex align-items-start gap-3">
        @if($icon)
            <span class="page-header__icon rounded-circle d-inline-flex align-items-center justify-content-center bg-primary-subtle text-primary">
                <i class="{{ $icon }} fs-4"></i>
            </span>
        @endif
        <div class="page-header__titles">
            <h1 class="page-title mb-0">{{ $title }}</h1>
            @if($subtitle)
                <p class="page-subtitle mb-0 text-secondary">{{ $subtitle }}</p>
            @endif
        </div>
    </div>

    @if(trim($slot))
        <div class="page-actions d-flex flex-wrap gap-2">
            {{ $slot }}
        </div>
    @endif
</div>
