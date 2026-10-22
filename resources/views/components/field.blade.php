@props(['name', 'label', 'type' => 'text', 'value' => null, 'help' => null, 'options' => []])

@php
    $id = $attributes->get('id', $name);
    $error = $errors->first($name);
    $describedBy = trim(($help ? "$id-help " : '').($error ? "$id-error" : ''));
    // Foco al primer campo con error tras un envío fallido (docs/DESIGN.md §8), sin JavaScript.
    $focus = $error && array_key_first($errors->getMessages()) === $name;
@endphp

<div>
    <label for="{{ $id }}" class="mb-1 block font-semibold">{{ $label }}</label>
    @if ($help)
        <p id="{{ $id }}-help" class="mb-1 text-sm text-muted">{{ $help }}</p>
    @endif

    @if ($type === 'textarea')
        <textarea id="{{ $id }}" name="{{ $name }}" @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif @if ($error) aria-invalid="true" @endif @if ($focus) autofocus @endif
                  {{ $attributes->merge(['class' => 'w-full rounded-field border border-line-strong bg-surface px-3 py-2', 'rows' => 4]) }}>{{ old($name, $value) }}</textarea>
    @elseif ($type === 'select')
        <select id="{{ $id }}" name="{{ $name }}" @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif @if ($error) aria-invalid="true" @endif @if ($focus) autofocus @endif
                {{ $attributes->merge(['class' => 'min-h-11 w-full rounded-field border border-line-strong bg-surface px-3']) }}>
            {{ $slot }}
            @foreach ($options as $optionValue => $optionLabel)
                <option value="{{ $optionValue }}" @selected((string) old($name, $value) === (string) $optionValue)>{{ $optionLabel }}</option>
            @endforeach
        </select>
    @else
        <input id="{{ $id }}" name="{{ $name }}" type="{{ $type }}" @if ($type !== 'password' && $type !== 'file') value="{{ old($name, $value) }}" @endif
               @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif @if ($error) aria-invalid="true" @endif @if ($focus) autofocus @endif
               {{ $attributes->merge(['class' => 'min-h-11 w-full rounded-field border border-line-strong bg-surface px-3']) }}>
    @endif

    @if ($error)
        <p id="{{ $id }}-error" class="mt-1 text-danger"><span aria-hidden="true">⚠</span> {{ $error }}</p>
    @endif
</div>
