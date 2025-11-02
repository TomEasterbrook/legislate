<?php

use App\Livewire\Concerns\ManagesPlayers;
use Livewire\Volt\Component;

new class extends Component {
    use ManagesPlayers;

    public string $gameCode = '';
    public string $hostName = '';
    public string $step = 'entering-name'; // 'entering-name' or 'lobby'

    public array $players = [];

    public function mount(): void
    {
        $this->gameCode = strtoupper(substr(md5(uniqid()), 0, 6));
    }

    public function setHostName(): void
    {
        $this->validate([
            'hostName' => 'required|min:1|max:50',
        ], [
            'hostName.required' => 'Please enter your name.',
            'hostName.min' => 'Name must be at least 1 character.',
            'hostName.max' => 'Name must not exceed 50 characters.',
        ]);

        $this->players = [
            ['name' => $this->hostName, 'color' => 'red'],
        ];

        $this->step = 'lobby';
    }

    public function getGameUrl(): string
    {
        return url('/game/join/'.$this->gameCode);
    }

    public function startGame(): void
    {
        $this->validatePlayers();

        // TODO: Start the multiplayer game
        $this->redirect('/game/multiplayer/'.$this->gameCode, navigate: true);
    }

    public function back(): void
    {
        if ($this->step === 'lobby') {
            $this->step = 'entering-name';
            $this->players = [];
            $this->hostName = '';
        } else {
            $this->redirect('/');
        }
    }
}; ?>

<div x-data="{ openColorPicker: null, copied: false }" class="flex items-center justify-center px-4 py-12">
    <div class="max-w-4xl w-full">
        @if ($step === 'entering-name')
            <!-- Name Entry Step -->
            <div class="text-center mb-8">
                <h2 class="text-3xl font-semibold text-gray-900 mb-3">
                    Create Multiplayer Game
                </h2>
                <p class="text-lg text-gray-600 mb-8">
                    Enter your name to start hosting a game
                </p>

                <div class="bg-white rounded-lg shadow-xl p-8 max-w-md mx-auto">
                    <div class="mb-6">
                        <label for="hostName" class="block text-sm font-medium text-gray-700 mb-2 text-left">
                            Your Name
                        </label>
                        <input
                            type="text"
                            id="hostName"
                            wire:model="hostName"
                            wire:keydown.enter="setHostName"
                            placeholder="Enter your name..."
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-lg"
                            maxlength="50"
                            autofocus
                        >
                        @error('hostName')
                            <span class="block text-sm text-red-600 mt-2 text-left">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex gap-3">
                        <button
                            type="button"
                            wire:click="back"
                            class="flex-1 py-3 px-6 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-semibold"
                        >
                            Back
                        </button>
                        <button
                            type="button"
                            wire:click="setHostName"
                            class="flex-1 py-3 px-6 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-semibold"
                        >
                            Continue
                        </button>
                    </div>
                </div>
            </div>
        @else
            <!-- Lobby Step -->
            <div class="mb-8">
                <div class="flex items-start justify-between mb-3">
                    <div class="text-left">
                        <h2 class="text-3xl font-semibold text-gray-900 mb-2">
                            Multiplayer Game Lobby
                        </h2>
                        <p class="text-lg text-gray-600">
                            Share the game code with others to join (2-6 players)
                        </p>
                    </div>

                    <div class="bg-white rounded-lg shadow-lg px-4 py-3 border-2 border-gray-200" x-data="{ codeCopied: false }">
                        <div class="flex items-center gap-3">
                            <div class="text-center">
                                <p class="text-xs font-medium text-gray-500 mb-1">Game Code</p>
                                <p class="text-3xl font-bold text-gray-900 tracking-wide" style="font-family: 'Quintessential', serif;">{{ $gameCode }}</p>
                            </div>
                            <button
                                type="button"
                                @click.prevent="navigator.clipboard.writeText('{{ $gameCode }}'); codeCopied = true; setTimeout(() => codeCopied = false, 2000)"
                                class="p-2 bg-gray-100 hover:bg-gray-200 rounded transition-colors relative text-gray-700 cursor-pointer"
                                title="Copy game code"
                            >
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                                <div
                                    x-show="codeCopied"
                                    x-transition
                                    class="absolute -bottom-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs py-1 px-2 rounded whitespace-nowrap"
                                    style="display: none;"
                                >
                                    Copied!
                                </div>
                            </button>
                        </div>
                    </div>
                </div>
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
                        :canRemove="false"
                        :showColorPicker="false"
                    />
                @endforeach

                <!-- Waiting for players placeholders -->
                @for ($i = count($players); $i < 6; $i++)
                    <div class="bg-gray-50 border-2 border-dashed border-gray-300 rounded-lg p-6 flex items-center justify-center">
                        <div class="text-center text-gray-400">
                            <svg class="w-12 h-12 mx-auto mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <p class="text-sm font-medium">Waiting for player...</p>
                        </div>
                    </div>
                @endfor
            </div>

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
                    class="flex-1 py-3 px-6 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-semibold"
                >
                    Start Game
                </button>
            </div>
        @endif
    </div>
</div>
