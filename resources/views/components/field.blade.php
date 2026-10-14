@props(['name', 'label', 'type' => 'text', 'value' => null, 'help' => null])

@php $id = $attributes->get('id', $name); @endphp

<div>
    <label for="{{ $id }}" class="mb-1 block font-semibold">{{ $label }}</label>
    @if ($help)
        <p id="{{ $id }}-help" class="mb-1 text-sm text-muted">{{ $help }}</p>
    @endif

    @if ($type === 'textarea')
        <textarea id="{{ $id }}" name="{{ $name }}" @if ($help) aria-describedby="{{ $id }}-help" @endif @error($name) aria-invalid="true" @enderror
                  {{ $attributes->merge(['class' => 'w-full rounded-field border border-line-strong bg-surface px-3 py-2', 'rows' => 4]) }}>{{ old($name, $value) }}</textarea>
    @else
        <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" @if ($type !== 'password' && $type !== 'file') value="{{ old($name, $value) }}" @endif
               @if ($help) aria-describedby="{{ $id }}-help" @endif @error($name) aria-invalid="true" @enderror
               {{ $attributes->merge(['class' => 'min-h-11 w-full rounded-field border border-line-strong bg-surface px-3']) }}>
    @endif

    @error($name)
        <p class="mt-1 text-danger"><span aria-hidden="true">⚠</span> {{ $message }}</p>
    @enderror
</div>
