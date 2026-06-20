@extends('errors.illustrated')

@section('title', 'Hmm...')
@section('first_digit', '5')
@section('last_digit', '2')

@section('message')
    It seems like something went wrong.<br>Please try again later.
@endsection

@section('svg')
<svg class="w-full h-full text-zinc-400" viewBox="0 0 130 180" fill="none" stroke="currentColor" xmlns="http://www.w3.org/2000/svg">
    <!-- Fingerprint Biometric Ridges (Top Half of the 0) -->
    <g stroke-linecap="round">
        <!-- Ridge 1 (Outermost curve) -->
        <path d="M 20 95 C 20 40, 110 40, 110 95" stroke-width="2" />
        
        <!-- Ridge 2 -->
        <path d="M 29 95 C 29 52, 101 52, 101 95" stroke-width="1.8" stroke-dasharray="45, 4, 15, 4" />
        
        <!-- Ridge 3 -->
        <path d="M 38 95 C 38 64, 92 64, 92 95" stroke-width="2" />
        
        <!-- Ridge 4 -->
        <path d="M 47 95 C 47 74, 83 74, 83 95" stroke-width="1.8" stroke-dasharray="10, 3, 20, 3" />
        
        <!-- Ridge 5 -->
        <path d="M 56 95 C 56 84, 74 84, 74 95" stroke-width="2.2" />
        
        <!-- Center Whorl Core -->
        <path d="M 65 95 C 65 90, 65 90, 65 95 Z" stroke-width="3.5" />
    </g>

    <!-- Support Stands for the Roadblock (Rendered behind the main plank) -->
    <g stroke-linecap="round">
        <!-- Left Leg Frame -->
        <line x1="28" y1="120" x2="16" y2="160" stroke-width="2.5" />
        <line x1="28" y1="120" x2="40" y2="160" stroke-width="1.5" opacity="0.6" />
        <line x1="10" y1="160" x2="46" y2="160" stroke-width="3.2" />
        
        <!-- Right Leg Frame -->
        <line x1="102" y1="120" x2="90" y2="160" stroke-width="2.5" />
        <line x1="102" y1="120" x2="114" y2="160" stroke-width="1.5" opacity="0.6" />
        <line x1="84" y1="160" x2="120" y2="160" stroke-width="3.2" />
    </g>

    <!-- Roadblock Barrier Main Plank (Horizontal Overlay) -->
    <g fill="var(--svg-bg-fill)" stroke="currentColor">
        <!-- Main Board -->
        <rect x="12" y="98" width="106" height="24" rx="3" stroke-width="2.2" />
        
        <!-- Diagonal Warning Stripes inside the Board -->
        <g stroke-width="3.5" stroke-linecap="round">
            <line x1="20" y1="98" x2="32" y2="122" />
            <line x1="36" y1="98" x2="48" y2="122" />
            <line x1="52" y1="98" x2="64" y2="122" />
            <line x1="68" y1="98" x2="80" y2="122" />
            <line x1="84" y1="98" x2="96" y2="122" />
            <line x1="100" y1="98" x2="112" y2="122" />
        </g>
    </g>
</svg>
@endsection
