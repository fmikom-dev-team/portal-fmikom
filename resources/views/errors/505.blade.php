@extends('errors.illustrated')

@section('title', 'Version Not Supported')
@section('first_digit', '5')
@section('last_digit', '5')

@section('message')
    The HTTP protocol version used in request is not supported.
@endsection

@section('svg')
<svg class="w-full h-full text-zinc-400" viewBox="0 0 130 180" fill="none" stroke="currentColor" xmlns="http://www.w3.org/2000/svg">
    <!-- Outer Zero Shape (Protocol Boundary) -->
    <path d="M 20 50 L 20 130 A 45 45 0 0 0 110 130 L 110 50 A 45 45 0 0 0 20 50 Z" stroke-width="2.2" stroke-linecap="round" />

    <!-- Globe Grid Structure (Representing HTTP / World Wide Web) -->
    <g stroke-linecap="round" opacity="0.8">
        <!-- Globe Outer Boundary -->
        <circle cx="65" cy="90" r="38" stroke-width="1.8" fill="currentColor" fill-opacity="0.04" />
        
        <!-- Longitude Rings -->
        <ellipse cx="65" cy="90" rx="18" ry="38" stroke-width="1.2" />
        <line x1="65" y1="52" x2="65" y2="128" stroke-width="1.5" />
        
        <!-- Latitude Rings -->
        <line x1="27" y1="90" x2="103" y2="90" stroke-width="1.5" />
        <path d="M 33 72 C 45 80, 85 80, 97 72" stroke-width="1" stroke-dasharray="2, 2" />
        <path d="M 33 108 C 45 100, 85 100, 97 108" stroke-width="1" stroke-dasharray="2, 2" />
    </g>

    <!-- Unsupported / Prohibition Sign overlay (Centered at 65, 90) -->
    <g class="text-rose-500">
        <!-- Prohibitive Circle -->
        <circle cx="65" cy="90" r="20" stroke="currentColor" stroke-width="3.2" fill="var(--svg-bg-fill)" />
        <!-- Diagonal Bar -->
        <line x1="51" y1="76" x2="79" y2="104" stroke="currentColor" stroke-width="3.2" />
    </g>
</svg>
@endsection
