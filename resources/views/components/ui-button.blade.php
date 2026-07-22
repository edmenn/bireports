@props(['type' => 'button', 'theme' => 'primary', 'label' => null, 'icon' => null, 'href' => null, 'size' => null])
@php($classes = 'btn btn-'.$theme.($size ? ' btn-'.$size : ''))
@if($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>@if($icon)<i class="{{ $icon }} me-1"></i>@endif{{ $label }}{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>@if($icon)<i class="{{ $icon }} me-1"></i>@endif{{ $label }}{{ $slot }}</button>
@endif
