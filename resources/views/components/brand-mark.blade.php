@props(['variant' => 'color'])

@php
    $shield = $variant === 'light' ? '#FFFFFF' : '#1E3A8A';
    $accent = $variant === 'light' ? '#60A5FA' : '#2563EB';
    $bars = $variant === 'light' ? '#F3F4F6' : '#E5E7EB';
    $gid = 'bm-'.\Illuminate\Support\Str::uuid()->toString();
@endphp

<svg
    {{ $attributes }}
    viewBox="0 0 64 64"
    xmlns="http://www.w3.org/2000/svg"
    role="img"
    aria-hidden="true"
>
    <defs>
        <linearGradient id="{{ $gid }}" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" style="stop-color: {{ $accent }}; stop-opacity:1" />
            <stop offset="100%" style="stop-color: {{ $shield }}; stop-opacity:1" />
        </linearGradient>
    </defs>
    <path fill="url(#{{ $gid }})" d="M32 4 8 14v18c0 14 10 26 24 28 14-2 24-14 24-28V14L32 4Z" opacity="0.95"/>
    <path fill="{{ $bars }}" d="M22 38V26h6v12h-6Zm8 6V22h6v22h-6Zm8-14V30h6v10h-6Z"/>
    <circle cx="32" cy="46" r="10" fill="white" fill-opacity="0.12"/>
    <path stroke="#10B981" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" fill="none" d="m26 46 6 6 12-14"/>
</svg>
