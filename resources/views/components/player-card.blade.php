@props(['index', 'player', 'colorData', 'availableColors', 'canRemove' => true, 'showColorPicker' => true])

<div
    wire:key="player-{{ $index }}"
    class="bg-white rounded-lg shadow-lg p-6 border-2 {{ $colorData['border'] }} transition-all hover:shadow-xl relative"
>
    <div class="flex items-center gap-4">
        <!-- Color Indicator / Game Piece with Color Picker -->
        <div class="relative">
            @if ($showColorPicker)
                <button
                    type="button"
                    @click="openColorPicker = (openColorPicker === {{ $index }} ? null : {{ $index }})"
                    class="w-16 h-16 rounded-full {{ $colorData['bg'] }} flex items-center justify-center text-white font-bold text-xl shadow-lg hover:scale-110 transition-transform cursor-pointer border-4 border-white"
                    title="Click to change color"
                >
                    {{ $index + 1 }}
                </button>

                <!-- Color Picker Popup -->
                <div
                    x-show="openColorPicker === {{ $index }}"
                    @click.away="openColorPicker = null"
                    x-transition
                    class="absolute left-0 top-full mt-2 bg-white rounded-lg shadow-xl p-4 z-50 border-2 border-gray-200 min-w-max"
                    style="display: none;"
                >
                    <div class="grid grid-cols-3 gap-3">
                        @foreach ($availableColors as $colorKey => $colorInfo)
                            <button
                                type="button"
                                wire:click="updatePlayerColor({{ $index }}, '{{ $colorKey }}')"
                                @click="openColorPicker = null"
                                class="w-12 h-12 rounded-full {{ $colorInfo['bg'] }} hover:scale-110 transition-transform border-2 {{ $player['color'] === $colorKey ? 'border-gray-800' : 'border-white' }} shadow-md shrink-0"
                                title="{{ $colorInfo['name'] }}"
                            ></button>
                        @endforeach
                    </div>
                </div>
            @else
                <div class="w-16 h-16 rounded-full {{ $colorData['bg'] }} flex items-center justify-center text-white font-bold text-xl shadow-lg border-4 border-white">
                    {{ $index + 1 }}
                </div>
            @endif
        </div>

        <!-- Player Name Input (Inline) -->
        <div class="flex-1">
            <input
                type="text"
                wire:model="players.{{ $index }}.name"
                placeholder="Enter player name..."
                class="w-full text-xl font-semibold text-gray-900 bg-transparent border-0 border-b-2 border-transparent hover:border-gray-300 focus:border-blue-500 focus:ring-0 px-0 py-1 transition-colors"
                maxlength="50"
            >
            @error('players.' . $index . '.name')
                <span class="block text-xs text-red-600 mt-1">{{ $message }}</span>
            @enderror
            @error('players.' . $index . '.color')
                <span class="block text-xs text-red-600 mt-1">{{ $message }}</span>
            @enderror
        </div>

        <!-- Remove Button -->
        @if ($canRemove)
            <button
                type="button"
                wire:click="removePlayer({{ $index }})"
                class="p-2 text-gray-400 hover:text-red-600 hover:bg-red-50 rounded-lg transition-colors shrink-0"
                title="Remove player"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        @endif
    </div>
</div>