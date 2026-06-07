@extends('errors.illustrated')

@section('title', 'Page Not Found')
@section('first_digit', '4')
@section('last_digit', '4')

@section('message')
    The page you are looking for is not available.
@endsection

@section('svg')
<svg class="w-full h-full text-zinc-400" viewBox="0 0 130 180" fill="none" stroke="currentColor" xmlns="http://www.w3.org/2000/svg">
    <!-- Outer Zero Shape (Search Space Boundary) -->
    <path d="M 20 50 L 20 130 A 45 45 0 0 0 110 130 L 110 50 A 45 45 0 0 0 20 50 Z" stroke-width="2.2" stroke-linecap="round" />

    <!-- Radar Coordinates / Search Grid (Representing scanning for the missing page) -->
    <g opacity="0.7">
        <!-- Concentric Scanning Rings -->
        <circle cx="65" cy="90" r="30" stroke-width="1.2" stroke-dasharray="4, 4" />
        <circle cx="65" cy="90" r="16" stroke-width="0.8" stroke-dasharray="2, 2" />
        
        <!-- Axis Coordinates -->
        <line x1="28" y1="90" x2="102" y2="90" stroke-width="0.8" stroke-dasharray="1, 3" />
        <line x1="65" y1="52" x2="65" y2="128" stroke-width="0.8" stroke-dasharray="1, 3" />
        
        <!-- Compass Ticks -->
        <line x1="65" y1="52" x2="65" y2="56" stroke-width="1.2" />
        <line x1="65" y1="124" x2="65" y2="128" stroke-width="1.2" />
        <line x1="28" y1="90" x2="32" y2="90" stroke-width="1.2" />
        <line x1="98" y1="90" x2="102" y2="90" stroke-width="1.2" />
    </g>

    <!-- Magnifying Glass Icon (Searching/Not Found indicator, tilted at 45 degrees) -->
    <g stroke-linecap="round">
        <!-- Handle -->
        <line x1="66.5" y1="91.5" x2="86" y2="111" stroke-width="3.2" />
        <!-- Handle Grip Accent -->
        <line x1="74" y1="99" x2="83" y2="108" stroke-width="1.5" opacity="0.6" />
        
        <!-- Glass Frame (Fills background to mask radar lines behind it) -->
        <circle cx="58" cy="83" r="12" stroke-width="2.5" fill="#121214" />
        
        <!-- Lens Glare/Highlight Arc -->
        <path d="M 50 83 A 8 8 0 0 1 58 75" stroke-width="1.2" />
        <!-- Small highlight dot -->
        <circle cx="61" cy="80" r="0.8" fill="currentColor" />
    </g>
</svg>
@endsection
