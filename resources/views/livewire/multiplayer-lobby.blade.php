<?php

use App\GameStatus;
use App\GameType;
use App\Livewire\Concerns\ManagesPlayers;
use App\Models\Game;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component {
    use ManagesPlayers;

    public string $gameCode = '';
    public string $hostName = '';
    public string $step = 'entering-name'; // 'entering-name' or 'lobby'
    public ?int $gameId = null;

    public array $players = [];

    public function mount(): void
    {
        $this->gameCode = strtoupper(substr(md5(uniqid()), 0, 6));

        $game = Game::create([
            'code' => $this->gameCode,
            'status' => GameStatus::Waiting,
            'game_type' => GameType::Multiplayer,
            'players' => [],
        ]);

        $this->gameId = $game->id;
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

        $game = Game::find($this->gameId);
        $game->addPlayer($this->hostName, 'red');

        // Store player info in session
        session(['game_'.$this->gameCode.'_player_name' => $this->hostName]);
        session(['game_'.$this->gameCode.'_player_index' => 0]);

        $this->step = 'lobby';
    }

    public function getGameUrl(): string
    {
        return url('/game/join/'.$this->gameCode);
    }

    public function startGame(): void
    {
        $this->validatePlayers();

        $game = Game::find($this->gameId);
        $game->status = GameStatus::InProgress;
        $game->save();

        \App\Events\GameStarted::dispatch($game);

        $this->redirect('/game/multiplayer/'.$this->gameCode, navigate: true);
    }

    public function back(): void
    {
        if ($this->step === 'lobby') {
            $this->leaveAndCleanup();
            $this->step = 'entering-name';
            $this->players = [];
            $this->hostName = '';
        } else {
            if ($this->gameId) {
                Game::destroy($this->gameId);
            }

            $this->redirect('/');
        }
    }

    public function leaveAndCleanup(): void
    {
        $game = Game::find($this->gameId);
        if ($game) {
            // Broadcast that host (player 0) is leaving
            if (count($game->players) > 0) {
                \App\Events\PlayerLeftGame::dispatch($game, 0);
            }

            // Clear all players when host leaves during waiting phase
            $game->players = [];
            $game->save();
        }

        // Clear session
        session()->forget('game_'.$this->gameCode.'_player_name');
        session()->forget('game_'.$this->gameCode.'_player_index');
    }

    #[On('echo:game.{gameCode},PlayerJoinedGame')]
    public function refreshPlayers(): void
    {
        $game = Game::find($this->gameId);
        if ($game) {
            $this->players = $game->fresh()->players ?? [];
        }
    }

    #[On('echo:game.{gameCode},PlayerLeftGame')]
    public function refreshPlayersLeft(): void
    {
        $game = Game::find($this->gameId);
        if ($game) {
            $this->players = $game->fresh()->players ?? [];
        }
    }
}; ?>

<div x-data="{
    openColorPicker: null,
    copied: false,
    init() {
        // Detect when host closes tab or navigates away from lobby
        window.addEventListener('beforeunload', (e) => {
            if ('{{ $step }}' === 'lobby') {
                @this.call('leaveAndCleanup');
            }
        });
    }
}" class="flex items-center justify-center px-4 py-12">
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
            <x-multiplayer.lobby-view :gameCode="$gameCode" :players="$players">
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

                <x-slot:actions>
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
                </x-slot:actions>
            </x-multiplayer.lobby-view>
        @endif
    </div>
</div>
