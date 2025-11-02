<?php

use Livewire\Volt\Component;

new class extends Component {
    public array $availableColors = [
        'red' => ['name' => 'Red', 'bg' => 'bg-red-500', 'border' => 'border-red-500', 'text' => 'text-red-500'],
        'blue' => ['name' => 'Blue', 'bg' => 'bg-blue-500', 'border' => 'border-blue-500', 'text' => 'text-blue-500'],
        'green' => ['name' => 'Green', 'bg' => 'bg-green-500', 'border' => 'border-green-500', 'text' => 'text-green-500'],
        'yellow' => ['name' => 'Yellow', 'bg' => 'bg-yellow-500', 'border' => 'border-yellow-500', 'text' => 'text-yellow-500'],
        'purple' => ['name' => 'Purple', 'bg' => 'bg-purple-500', 'border' => 'border-purple-500', 'text' => 'text-purple-500'],
        'orange' => ['name' => 'Orange', 'bg' => 'bg-orange-500', 'border' => 'border-orange-500', 'text' => 'text-orange-500'],
    ];

    public array $players = [
        ['name' => '', 'color' => 'red'],
        ['name' => '', 'color' => 'blue'],
    ];

    public function addPlayer(): void
    {
        if (count($this->players) < 6) {
            $nextColor = $this->getNextAvailableColor();
            $this->players[] = ['name' => '', 'color' => $nextColor];
        }
    }

    public function removePlayer(int $index): void
    {
        if (count($this->players) > 2) {
            unset($this->players[$index]);
            $this->players = array_values($this->players);
        }
    }

    public function canAddPlayer(): bool
    {
        return count($this->players) < 6;
    }

    public function canRemovePlayer(): bool
    {
        return count($this->players) > 2;
    }

    public function getNextAvailableColor(): string
    {
        $usedColors = array_column($this->players, 'color');
        $availableColorKeys = array_keys($this->availableColors);

        foreach ($availableColorKeys as $color) {
            if (!in_array($color, $usedColors)) {
                return $color;
            }
        }

        return $availableColorKeys[0];
    }

    public function getAvailableColorsForPlayer(int $index): array
    {
        $usedColors = array_column($this->players, 'color');
        $currentPlayerColor = $this->players[$index]['color'];

        return array_filter(
            $this->availableColors,
            fn ($key) => !in_array($key, $usedColors) || $key === $currentPlayerColor,
            ARRAY_FILTER_USE_KEY
        );
    }

    public function getColorData(string $color): array
    {
        return $this->availableColors[$color] ?? $this->availableColors['red'];
    }

    public function updatePlayerColor(int $index, string $color): void
    {
        if (isset($this->players[$index]) && array_key_exists($color, $this->availableColors)) {
            $this->players[$index]['color'] = $color;
        }
    }

    public function startGame(): void
    {
        $this->validate([
            'players.*.name' => 'required|min:1|max:50',
            'players.*.color' => 'required|in:' . implode(',', array_keys($this->availableColors)),
        ], [
            'players.*.name.required' => 'All players must have a name.',
            'players.*.name.min' => 'Player names must be at least 1 character.',
            'players.*.name.max' => 'Player names must not exceed 50 characters.',
            'players.*.color.required' => 'All players must have a color.',
            'players.*.color.in' => 'Invalid color selected.',
        ]);

        // TODO: Start the game with the players
        $this->redirect('/game/local', navigate: true);
    }

    public function back(): void
    {
        $this->redirect('/');
    }
}; ?>

<div class="flex items-center justify-center px-4 py-12" x-data="{ openColorPicker: null }">
    <div class="max-w-4xl w-full">
        <!-- Header -->
        <div class="text-center mb-8">
            <h2 class="text-3xl font-semibold text-gray-900 mb-3">
                Local Game Lobby
            </h2>
            <p class="text-lg text-gray-600">
                Add players to start your local game (2-6 players)
            </p>
        </div>

        <!-- Players Counter -->
        <div class="text-center mb-6">
            <span class="inline-block px-4 py-2 bg-gray-100 rounded-full text-gray-700 font-semibold">
                {{ count($players) }} / 6 Players
            </span>
        </div>

        <!-- Player Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            @foreach ($players as $index => $player)
                @php
                    $colorData = $this->getColorData($player['color']);
                @endphp
                <div
                    wire:key="player-{{ $index }}"
                    class="bg-white rounded-lg shadow-lg p-6 border-2 {{ $colorData['border'] }} transition-all hover:shadow-xl relative"
                >
                    <div class="flex items-center gap-4">
                        <!-- Color Indicator / Game Piece with Color Picker -->
                        <div class="relative">
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
                                    @foreach ($this->getAvailableColorsForPlayer($index) as $colorKey => $colorInfo)
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
                        @if ($this->canRemovePlayer())
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
            @endforeach
        </div>

        <!-- Add Player Button -->
        @if ($this->canAddPlayer())
            <button
                type="button"
                wire:click="addPlayer"
                class="w-full py-4 mb-6 border-2 border-dashed border-gray-300 rounded-lg text-gray-600 hover:border-blue-500 hover:text-blue-500 hover:bg-blue-50 transition-all flex items-center justify-center gap-2 font-semibold"
            >
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Add Another Player
            </button>
        @endif

        <!-- Action Buttons -->
        <div class="flex gap-4">
            <button
                type="button"
                wire:click="back"
                class="flex-1 py-3 px-6 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-semibold"
            >
                Back
            </button>
            <button
                type="button"
                wire:click="startGame"
                class="flex-1 py-3 px-6 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-semibold"
            >
                Start Game
            </button>
        </div>
    </div>
</div>
