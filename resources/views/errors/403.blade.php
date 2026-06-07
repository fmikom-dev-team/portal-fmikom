@extends('errors.illustrated')

@section('title', 'Access Denied')
@section('first_digit', '4')
@section('last_digit', '3')

@section('message')
    You don't have access to view this page.<br>Do not peep.
@endsection

@section('svg')
<svg class="w-full h-full text-zinc-400" viewBox="0 0 130 180" fill="none" stroke="currentColor" xmlns="http://www.w3.org/2000/svg">
    <!-- Outer Zero Shape (Vault Outer Door Outline) -->
    <path d="M 20 50 L 20 130 A 45 45 0 0 0 110 130 L 110 50 A 45 45 0 0 0 20 50 Z" stroke-width="2.2" stroke-linecap="round" />
    
    <!-- Inner Zero Shape -->
    <path d="M 32 55 L 32 125 A 33 33 0 0 0 98 125 L 98 55 A 33 33 0 0 0 32 55 Z" stroke-width="1.2" stroke-dasharray="3, 3" />

    <!-- Outer target arcs/handles (Cardinal bracket accents) -->
    <path d="M 45 15 A 20 20 0 0 1 85 15" stroke-width="1.8" stroke-linecap="round" />
    <path d="M 45 165 A 20 20 0 0 0 85 165" stroke-width="1.8" stroke-linecap="round" />
    <path d="M 8 75 A 20 20 0 0 0 8 105" stroke-width="1.8" stroke-linecap="round" />
    <path d="M 122 75 A 20 20 0 0 1 122 105" stroke-width="1.8" stroke-linecap="round" />

    <!-- Crosshair Lines -->
    <line x1="12" y1="90" x2="118" y2="90" stroke-width="0.8" stroke-dasharray="2, 4" />
    <line x1="65" y1="20" x2="65" y2="160" stroke-width="0.8" stroke-dasharray="2, 4" />

    <!-- Dial Tick Marks Ring (Dashed circle) -->
    <circle cx="65" cy="90" r="34" stroke-width="1.5" stroke-dasharray="2, 4" />
    
    <!-- Concentric Vault Wheels -->
    <circle cx="65" cy="90" r="25" stroke-width="2" />
    <circle cx="65" cy="90" r="15" stroke-width="1.2" stroke-dasharray="6, 2" />
    <circle cx="65" cy="90" r="7" fill="currentColor" />

    <!-- Locking Bolt Pins (extending from the wheel) -->
    <line x1="65" y1="65" x2="65" y2="52" stroke-width="2.5" stroke-linecap="round" />
    <line x1="65" y1="115" x2="65" y2="128" stroke-width="2.5" stroke-linecap="round" />
    <line x1="40" y1="90" x2="27" y2="90" stroke-width="2.5" stroke-linecap="round" />
    <line x1="90" y1="90" x2="103" y2="90" stroke-width="2.5" stroke-linecap="round" />

    <!-- Diagonal cross alignment notches -->
    <line x1="47.3" y1="72.3" x2="38.5" y2="63.5" stroke-width="1.5" stroke-linecap="round" />
    <line x1="82.7" y1="72.3" x2="91.5" y2="63.5" stroke-width="1.5" stroke-linecap="round" />
    <line x1="47.3" y1="107.7" x2="38.5" y2="116.5" stroke-width="1.5" stroke-linecap="round" />
    <line x1="82.7" y1="107.7" x2="91.5" y2="116.5" stroke-width="1.5" stroke-linecap="round" />
</svg>
@endsection
