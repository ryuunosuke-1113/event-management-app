@props(['status', 'label'])

@php
    $class = match ($status) {
        'pending_payment', 'pending', 'draft' => 'badge-pending',

        'confirmed', 'published' => 'badge-confirmed',

        'paid' => 'badge-paid',

        'cancelled', 'closed' => 'badge-cancelled',

        'failed' => 'badge-failed',

        'refunded', 'finished' => 'badge-refunded',

        default => 'badge-refunded',
    };
@endphp
<span class="badge {{ $class }}">
    {{ $label }}
</span>
