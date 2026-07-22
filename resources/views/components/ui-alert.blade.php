@props(['theme' => 'info', 'title' => null, 'icon' => null, 'dismissable' => false])
<div {{ $attributes->class(['alert', 'alert-'.$theme, 'alert-dismissible' => $dismissable]) }} role="alert">
    @if($icon)<i class="{{ $icon }} me-2"></i>@endif
    @if($title)<strong>{{ $title }}</strong>@endif
    {{ $slot }}
    @if($dismissable)<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('common.close') }}"></button>@endif
</div>
