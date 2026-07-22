@props(['name', 'label' => null, 'placeholder' => null, 'fgroupClass' => 'mb-3', 'id' => null])
@php($fieldId = $id ?: $name)
<div class="{{ $fgroupClass }}">
    @if($label)<label class="form-label" for="{{ $fieldId }}">{{ $label }}</label>@endif
    <div class="input-group">
        @isset($prependSlot){{ $prependSlot }}@endisset
        <input name="{{ $name }}" id="{{ $fieldId }}" type="file" aria-label="{{ $placeholder ?: $label }}" {{ $attributes->except(['class'])->class(['form-control', 'is-invalid' => $errors->has($name)]) }}>
    </div>
    @error($name)<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
    @isset($bottom)<div class="form-hint">{{ $bottom }}</div>@endisset
</div>
