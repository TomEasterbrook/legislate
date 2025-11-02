@props(['title', 'description', 'color' => 'blue', 'wireClick'])

@php
    $colorClasses = [
        'blue' => 'hover:border-blue-500 text-blue-600',
        'indigo' => 'hover:border-indigo-500 text-indigo-600',
        'teal' => 'hover:border-teal-500 text-teal-600',
    ];

    $selectedColor = $colorClasses[$color] ?? $colorClasses['blue'];
    [$borderClass, $iconClass] = explode(' ', $selectedColor);
@endphp

<button
    wire:click="{{ $wireClick }}"
    class="w-full bg-white rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 p-8 text-left group border-2 border-gray-200 {{ $borderClass }}"
>
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-semibold text-gray-900 mb-2">
                {{ $title }}
            </h2>
            <p class="text-gray-600">
                {{ $description }}
            </p>
        </div>
        <svg class="w-8 h-8 {{ $iconClass }} transform group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
        </svg>
    </div>
</button>
