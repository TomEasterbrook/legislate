@props(['showBack' => false, 'backUrl' => '/', 'backLabel' => 'Back'])

<header class="border-b border-gray-200 bg-white/80 backdrop-blur-sm sticky top-0 z-10">
    <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
        <h1 class="text-3xl font-bold text-gray-900" style="font-family: 'Quintessential', serif; letter-spacing: 0.5px;">
            Legislate?!
        </h1>

        @if($showBack)
            <a href="{{ $backUrl }}" wire:navigate class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 rounded-lg transition-colors">
                {{ $backLabel }}
            </a>
        @endif
    </div>
</header>
