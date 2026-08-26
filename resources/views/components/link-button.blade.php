@props(['href', 'variant' => 'primary'])

@php
    $class = match ($variant) {
        'primary' => 'btn btn-primary',
        'secondary' => 'btn btn-secondary',
        'danger' => 'btn btn-danger',
        default => 'btn btn-primary',
    };
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $class]) }}>
    {{ $slot }}
</a>
