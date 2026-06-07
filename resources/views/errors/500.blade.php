@extends('errors.illustrated')

@section('title', 'Internal Server Error')
@section('first_digit', '5')
@section('last_digit', '0')

@section('message')
    Something went wrong on our end.<br>We are working to fix it.
@endsection

@section('svg')
<svg class="w-full h-full text-zinc-400" viewBox="0 0 130 180" fill="none" stroke="currentColor" xmlns="http://www.w3.org/2000/svg">
    <!-- Outer Zero Shape (System Boundary) -->
    <path d="M 20 50 L 20 130 A 45 45 0 0 0 110 130 L 110 50 A 45 45 0 0 0 20 50 Z" stroke-width="2.2" stroke-linecap="round" />

    <!-- Distressed Network Nodes (Fractured System Connections) -->
    <!-- Node Top-Left -->
    <circle cx="30" cy="40" r="3.5" fill="currentColor" opacity="0.8" />
    <path d="M 30 40 L 45 48" stroke-width="1" stroke-dasharray="2, 2" />
    
    <!-- Node Top-Right -->
    <circle cx="100" cy="40" r="3.5" fill="currentColor" opacity="0.8" />
    <path d="M 100 40 L 85 48" stroke-width="1" />
    
    <!-- Node Bottom-Left (Disconnected/Jagged Line) -->
    <circle cx="28" cy="140" r="3.5" fill="currentColor" opacity="0.8" />
    <path d="M 28 140 L 38 135 L 45 130" stroke-width="1.2" stroke-dasharray="2, 2" />
    
    <!-- Node Bottom-Right -->
    <circle cx="102" cy="140" r="3.5" fill="currentColor" opacity="0.8" />
    <path d="M 102 140 L 85 130" stroke-width="1.2" />

    <!-- Server Rack Cabinet in center -->
    <g>
        <rect x="42" y="48" width="46" height="84" rx="4" fill="#121214" stroke-width="2" />
        
        <!-- Shelf 1 -->
        <line x1="48" y1="58" x2="82" y2="58" stroke-width="1.2" />
        <circle cx="74" cy="58" r="1" fill="currentColor" />
        <circle cx="79" cy="58" r="1" fill="currentColor" />
        
        <!-- Shelf 2 -->
        <line x1="48" y1="70" x2="82" y2="70" stroke-width="1.2" />
        <circle cx="74" cy="70" r="1" fill="currentColor" />
        <circle cx="79" cy="70" r="1" fill="currentColor" />
        
        <!-- Shelf 3 (Alert Zone) -->
        <line x1="48" y1="82" x2="60" y2="82" stroke-width="1.2" stroke-dasharray="2, 1" />
        
        <!-- Shelf 4 -->
        <line x1="48" y1="94" x2="82" y2="94" stroke-width="1.2" />
        <circle cx="74" cy="94" r="1" fill="currentColor" />
        <circle cx="79" cy="94" r="1" fill="currentColor" />

        <!-- Shelf 5 -->
        <line x1="48" y1="106" x2="82" y2="106" stroke-width="1.2" />
        <circle cx="74" cy="106" r="1" fill="currentColor" />
        <circle cx="79" cy="106" r="1" fill="currentColor" />
        
        <!-- Shelf 6 -->
        <line x1="48" y1="118" x2="82" y2="118" stroke-width="1.2" />
        <circle cx="74" cy="118" r="1" fill="currentColor" />
        <circle cx="79" cy="118" r="1" fill="currentColor" />
    </g>

    <!-- Warning / Error Hazard Sign overlay (Centered at 65, 88) -->
    <g class="text-rose-500">
        <!-- Triangle Base -->
        <path d="M 65 72 L 81 99 L 49 99 Z" fill="#121214" stroke="currentColor" stroke-width="2" stroke-linejoin="round" />
        <!-- Exclamation Point -->
        <line x1="65" y1="80" x2="65" y2="89" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" />
        <circle cx="65" cy="94" r="1.2" fill="currentColor" />
    </g>
</svg>
@endsection
