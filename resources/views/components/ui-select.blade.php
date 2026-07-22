@props(['name', 'label' => null, 'fgroupClass' => 'mb-3', 'id' => null])
@php($fieldId = $id ?: $name)
<div class="{{ $fgroupClass }}">
    @if($label)<label class="form-label" for="{{ $fieldId }}">{{ $label }}</label>@endif
    <select name="{{ $name }}" id="{{ $fieldId }}" {{ $attributes->except(['class'])->class(['form-select', 'is-invalid' => $errors->has($name)]) }}>{{ $slot }}</select>
    @error($name)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    @isset($bottom)<div class="form-hint">{{ $bottom }}</div>@endisset
</div>
