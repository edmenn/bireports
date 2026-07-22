@props(['title' => null, 'icon' => null, 'theme' => null, 'themeMode' => null, 'headerClass' => '', 'bodyClass' => '', 'footerClass' => ''])
<div {{ $attributes->class(['card', 'card-'.$theme => $theme && $themeMode !== 'outline']) }}>
    @if($title || $icon || isset($toolsSlot))
        <div class="card-header {{ $headerClass }}">
            <div class="d-flex align-items-center justify-content-between gap-3 w-100">
                <h3 class="card-title mb-0">@if($icon)<i class="{{ $icon }} me-2"></i>@endif{{ $title }}</h3>
                @isset($toolsSlot)<div class="card-actions">{{ $toolsSlot }}</div>@endisset
            </div>
        </div>
    @endif
    <div class="card-body {{ $bodyClass }}">{{ $slot }}</div>
    @isset($footerSlot)<div class="card-footer {{ $footerClass }}">{{ $footerSlot }}</div>@endisset
</div>
