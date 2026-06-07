@extends('errors.illustrated')

@section('title', 'Service Unavailable')
@section('first_digit', '5')
@section('last_digit', '3')

@section('message')
    The server is temporarily busy.<br>Please try again later.
@endsection

@section('svg')
<svg class="w-full h-full text-zinc-400" viewBox="0 0 130 180" fill="none" stroke="currentColor" xmlns="http://www.w3.org/2000/svg">
    <!-- Concentric U-shapes representing building skeleton / foundation -->
    <path d="M 22 45 L 22 125 A 38 38 0 0 0 98 125" stroke-width="2" stroke-linecap="round" />
    <path d="M 34 45 L 34 125 A 26 26 0 0 0 86 125 L 86 60" stroke-width="1.8" stroke-linecap="round" />
    <path d="M 46 45 L 46 125 A 14 14 0 0 0 74 125 L 74 75" stroke-width="1.5" stroke-linecap="round" />
    <path d="M 58 45 L 58 125 A 2 2 0 0 0 62 125 L 62 90" stroke-width="1" stroke-linecap="round" />

    <!-- Left side outer vertical extension of the 0 -->
    <path d="M 22 45 L 22 35" stroke-width="2" stroke-linecap="round" />
    
    <!-- Crane Tower (Mast) on the right side -->
    <!-- Columns -->
    <line x1="98" y1="15" x2="98" y2="160" stroke-width="2.2" stroke-linecap="round" />
    <line x1="112" y1="15" x2="112" y2="160" stroke-width="2.2" stroke-linecap="round" />
    
    <!-- Horizontals & Cross braces -->
    <line x1="98" y1="15" x2="112" y2="15" stroke-width="1.5" />
    <line x1="98" y1="35" x2="112" y2="35" stroke-width="1.5" />
    <line x1="98" y1="55" x2="112" y2="55" stroke-width="1.5" />
    <line x1="98" y1="75" x2="112" y2="75" stroke-width="1.5" />
    <line x1="98" y1="95" x2="112" y2="95" stroke-width="1.5" />
    <line x1="98" y1="115" x2="112" y2="115" stroke-width="1.5" />
    <line x1="98" y1="135" x2="112" y2="135" stroke-width="1.5" />
    <line x1="98" y1="155" x2="112" y2="155" stroke-width="1.5" />
    
    <!-- Diagonals (X-Bracing) -->
    <line x1="98" y1="15" x2="112" y2="35" stroke-width="1" />
    <line x1="112" y1="15" x2="98" y2="35" stroke-width="1" />
    <line x1="98" y1="35" x2="112" y2="55" stroke-width="1" />
    <line x1="112" y1="35" x2="98" y2="55" stroke-width="1" />
    <line x1="98" y1="55" x2="112" y2="75" stroke-width="1" />
    <line x1="112" y1="55" x2="98" y2="75" stroke-width="1" />
    <line x1="98" y1="75" x2="112" y2="95" stroke-width="1" />
    <line x1="112" y1="75" x2="98" y2="95" stroke-width="1" />
    <line x1="98" y1="95" x2="112" y2="115" stroke-width="1" />
    <line x1="112" y1="95" x2="98" y2="115" stroke-width="1" />
    <line x1="98" y1="115" x2="112" y2="135" stroke-width="1" />
    <line x1="112" y1="115" x2="98" y2="135" stroke-width="1" />
    <line x1="98" y1="135" x2="112" y2="155" stroke-width="1" />
    <line x1="112" y1="135" x2="98" y2="155" stroke-width="1" />

    <!-- Crane Jib at the top -->
    <!-- Main Jib Cord -->
    <line x1="15" y1="20" x2="125" y2="20" stroke-width="2.5" stroke-linecap="round" />
    <!-- Lower Jib Cord -->
    <line x1="25" y1="26" x2="112" y2="26" stroke-width="1.2" />
    <!-- Jib bracing -->
    <path d="M 25 20 L 35 26 M 45 20 L 35 26 M 45 20 L 55 26 M 65 20 L 55 26 M 65 20 L 75 26 M 85 20 L 75 26 M 85 20 L 95 26 M 98 20 L 95 26" stroke-width="0.8" />

    <!-- Apex / Tower Peak -->
    <path d="M 98 15 L 105 3 L 112 15" stroke-width="1.8" fill="none" />
    <!-- Guy Wires -->
    <line x1="105" y1="3" x2="20" y2="20" stroke-width="0.8" stroke-dasharray="2, 2" />
    <line x1="105" y1="3" x2="122" y2="20" stroke-width="0.8" stroke-dasharray="2, 2" />

    <!-- Trolley & Hook Line -->
    <rect x="52" y="26" width="8" height="3" fill="currentColor" />
    <line x1="56" y1="29" x2="56" y2="70" stroke-width="1" stroke-dasharray="2, 1" />
    <!-- Hook -->
    <path d="M 54 70 C 54 73, 58 73, 58 70 C 58 68, 56 68, 56 68" stroke-width="1.5" stroke-linecap="round" />
</svg>
@endsection
