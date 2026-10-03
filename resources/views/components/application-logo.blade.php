@props(['variant' => 'color'])

@if ($variant === 'white')
    <img src="{{ asset('images/logo-white.png') }}" alt="{{ config('app.name', 'Cressco') }}" {{ $attributes->merge(['class' => 'h-9 w-auto object-contain']) }}>
@else
    <img src="{{ asset('images/logo.png') }}" alt="{{ config('app.name', 'Cressco') }}" {{ $attributes->merge(['class' => 'h-9 w-auto object-contain']) }}>
@endif
