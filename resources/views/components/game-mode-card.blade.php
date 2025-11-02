@props(['title', 'description', 'color' => 'blue', 'wireClick', 'icon'])

@php
    $colorClasses = [
        'blue' => 'hover:border-blue-500',
        'indigo' => 'hover:border-indigo-500',
        'teal' => 'hover:border-teal-500',
    ];

    $borderClass = $colorClasses[$color] ?? $colorClasses['blue'];
@endphp

<button
    wire:click="{{ $wireClick }}"
    class="w-full bg-white rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 p-8 text-left group border-2 border-gray-200 {{ $borderClass }}"
>
    <div class="flex items-center gap-6">
        <div class="shrink-0">
            <div class="w-16 h-16 flex items-center justify-center group-hover:scale-110 transition-transform text-gray-900">
                {{ $icon }}
            </div>
        </div>
        <div class="flex-1">
            <h2 class="text-2xl font-semibold text-gray-900 mb-2">
                {{ $title }}
            </h2>
            <p class="text-gray-600">
                {{ $description }}
            </p>
        </div>
    </div>
</button>
