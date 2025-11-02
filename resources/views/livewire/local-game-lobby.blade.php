<?php

use App\Livewire\Concerns\ManagesPlayers;
use Livewire\Volt\Component;

new class extends Component {
    use ManagesPlayers;

    public array $players = [
        ['name' => '', 'color' => 'red'],
        ['name' => '', 'color' => 'blue'],
    ];

    public function addPlayer(): void
    {
        if ($this->canAddPlayer()) {
            $nextColor = $this->getNextAvailableColor();
            $this->players[] = ['name' => '', 'color' => $nextColor];
        }
    }

    public function startGame(): void
    {
        $this->validatePlayers();

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
                <x-player-card
                    :index="$index"
                    :player="$player"
                    :colorData="$this->getColorData($player['color'])"
                    :availableColors="$this->getAvailableColorsForPlayer($index)"
                    :canRemove="$this->canRemovePlayer()"
                />
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
