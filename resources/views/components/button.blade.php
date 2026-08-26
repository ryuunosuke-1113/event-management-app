@props([
    'type' => 'button',
    'variant' => 'primary',
])

@php
    $class = match ($variant) {
        'primary' => 'btn btn-primary',
        'secondary' => 'btn btn-secondary',
        'danger' => 'btn btn-danger',
        default => 'btn btn-primary',
    };
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => $class]) }}>
    {{ $slot }}
</button>
