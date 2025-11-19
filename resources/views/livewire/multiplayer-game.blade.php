<?php

use App\GameStatus;
use App\Livewire\Concerns\ManagesPlayers;
use App\Models\Game;
use Livewire\Attributes\On;
use Livewire\Volt\Component;

new class extends Component {
    use ManagesPlayers;

    public string $gameCode = '';
    public ?Game $game = null;
    public array $players = [];
    public bool $isHost = false;
    public ?string $myPlayerName = null;
    public ?int $myPlayerIndex = null;
    public bool $hasJoined = false;
    public string $playerName = '';
    public string $playerColor = 'red';

    public function mount(string $code): void
    {
        $this->gameCode = strtoupper($code);

        $this->game = Game::where('code', $this->gameCode)->first();

        if (! $this->game) {
            $this->redirect('/', navigate: true);

            return;
        }

        $this->players = $this->game->players ?? [];

        // Always start with not joined - users must enter name each time
        $this->hasJoined = false;
        
        // Check session for existing join
        $savedName = session('multiplayer_player_name');
        if ($savedName) {
            foreach ($this->players as $index => $player) {
                if ($player['name'] === $savedName) {
                    $this->playerName = $savedName;
                    $this->myPlayerName = $savedName;
                    $this->myPlayerIndex = $index;
                    $this->isHost = $index === 0;
                    $this->hasJoined = true;
                    $this->playerColor = $player['color'];
                    break;
                }
            }
        }

        // Get next available color for when they join
        $this->playerColor = $this->getNextAvailableColor();
    }

    public function joinGame(): void
    {
        $this->validate([
            'playerName' => 'required|min:1|max:50',
        ], [
            'playerName.required' => 'Please enter your name.',
            'playerName.min' => 'Name must be at least 1 character.',
            'playerName.max' => 'Name must not exceed 50 characters.',
        ]);

        // Refresh game to check status
        $this->game = $this->game->fresh();

        if (! $this->game || $this->game->status !== GameStatus::Waiting || $this->game->isFull()) {
            $this->addError('playerName', 'Unable to join game. Please try again.');

            return;
        }

        $playerIndex = $this->game->getPlayerCount();
        $this->game->addPlayer($this->playerName, $this->playerColor);

        // Refresh game to get updated players array
        $this->game->refresh();

        // Save to session so we remember who we are
        session(['multiplayer_player_name' => $this->playerName]);

        // Update local state (only in memory, not in session)
        $this->myPlayerName = $this->playerName;
        $this->myPlayerIndex = $playerIndex;
        $this->isHost = $playerIndex === 0;
        $this->hasJoined = true;
        $this->players = $this->game->players ?? [];

        // Broadcast to other players
        \App\Events\PlayerJoinedGame::dispatch($this->game, $this->playerName, $this->playerColor);
    }

    public function startGame(): void
    {
        if (! $this->isHost || ! $this->game->hasMinimumPlayers()) {
            return;
        }

        $this->game->status = GameStatus::InProgress;
        $this->game->save();

        // Broadcast to all players that the game has started
        \App\Events\GameStarted::dispatch($this->game);

        // Redirect host to game board
        $this->redirect('/game/multiplayer-play?code='.$this->gameCode, navigate: true);
    }

    public function leaveGame(): void
    {
        $this->removeMyPlayer();
        $this->redirect('/', navigate: true);
    }

    public function removeMyPlayer(): void
    {
        if ($this->myPlayerName && $this->game) {
            // Find the current player's actual index in the array
            $currentIndex = null;
            foreach ($this->game->players as $index => $player) {
                if ($player['name'] === $this->myPlayerName) {
                    $currentIndex = $index;
                    break;
                }
            }

            if ($currentIndex !== null) {
                $this->game->removePlayer($currentIndex);
                \App\Events\PlayerLeftGame::dispatch($this->game, $currentIndex);
            }
        }
    }

    #[On('echo:game.{gameCode},PlayerJoinedGame')]
    public function refreshPlayersJoined(): void
    {
        if ($this->game) {
            $this->game = $this->game->fresh();
            $this->players = $this->game->players ?? [];
        }
    }

    #[On('echo:game.{gameCode},PlayerLeftGame')]
    public function refreshPlayersLeft(): void
    {
        if ($this->game) {
            $this->game = $this->game->fresh();
            $this->players = $this->game->players ?? [];

            // If host left (no players), redirect everyone home
            if (count($this->players) === 0) {
                $this->redirect('/', navigate: true);

                return;
            }

            // Update my player index based on current position
            if ($this->myPlayerName) {
                foreach ($this->players as $index => $player) {
                    if ($player['name'] === $this->myPlayerName) {
                        $this->myPlayerIndex = $index;
                        break;
                    }
                }
            }
        }
    }

    #[On('echo:game.{gameCode},GameStarted')]
    public function handleGameStarted(): void
    {
        // Redirect all players to the game board
        $this->redirect('/game/multiplayer-play?code='.$this->gameCode, navigate: true);
    }

    public function getSubtitle(): string
    {
        if ($this->game->status === GameStatus::Waiting) {
            return 'Waiting for players to join (2-6 players)';
        } elseif ($this->game->status === GameStatus::InProgress) {
            return 'Game in progress';
        } else {
            return 'Game '.strtolower($this->game->status->value);
        }
    }
}; ?>

<div x-data="{
    init() {
        // Detect when user closes tab or navigates away
        window.addEventListener('beforeunload', (e) => {
            // Call Livewire method to remove player if they've joined
            if ({{ $hasJoined ? 'true' : 'false' }}) {
                @this.call('removeMyPlayer');
            }
        });
    }
}" class="flex items-center justify-center px-4 py-12">
    <div class="max-w-4xl w-full">
        @if (!$hasJoined)
            <!-- Name Entry Screen -->
            <div class="text-center mb-8">
                <h2 class="text-3xl font-semibold text-gray-900 mb-3">
                    {{ count($players) === 0 ? 'Create Multiplayer Game' : 'Join Game' }}
                </h2>

                <!-- Game Code Display -->
                <div class="inline-block bg-white rounded-lg shadow-lg px-4 py-3 border-2 border-gray-200 mb-6">
                    <p class="text-xs font-medium text-gray-500 mb-1">Game Code</p>
                    <p class="text-3xl font-bold text-gray-900 tracking-wide" style="font-family: 'Quintessential', serif;">{{ $gameCode }}</p>
                </div>

                <p class="text-lg text-gray-600 mb-8">
                    Enter your name to {{ count($players) === 0 ? 'start hosting' : 'join' }} the game
                </p>

                <div class="bg-white rounded-lg shadow-xl p-8 max-w-md mx-auto">
                    <div class="mb-6">
                        <label for="playerName" class="block text-sm font-medium text-gray-700 mb-2 text-left">
                            Your Name
                        </label>
                        <input
                            type="text"
                            id="playerName"
                            wire:model="playerName"
                            wire:keydown.enter="joinGame"
                            placeholder="Enter your name..."
                            class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-lg"
                            maxlength="50"
                            autofocus
                        >
                        @error('playerName')
                            <span class="block text-sm text-red-600 mt-2 text-left">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="flex gap-3">
                        <a
                            href="/"
                            wire:navigate
                            class="flex-1 py-3 px-6 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-semibold text-center"
                        >
                            Back
                        </a>
                        <button
                            type="button"
                            wire:click="joinGame"
                            class="flex-1 py-3 px-6 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-semibold"
                        >
                            Continue
                        </button>
                    </div>
                </div>
            </div>
        @else
            <!-- Lobby Screen -->
            <x-multiplayer.lobby-view :gameCode="$gameCode" :players="$players" :subtitle="$this->getSubtitle()">
                @foreach ($players as $index => $player)
                    <x-player-card
                        wire:key="player-{{ $index }}-{{ $player['name'] }}"
                        :index="$index"
                        :player="$player"
                        :colorData="$this->getColorData($player['color'])"
                        :availableColors="$this->getAvailableColorsForPlayer($index)"
                        :canRemove="false"
                        :showColorPicker="false"
                        :readOnly="true"
                    />
                @endforeach

                <x-slot:actions>
                    <div class="flex gap-4">
                        <button
                            type="button"
                            wire:click="leaveGame"
                            class="flex-1 py-3 px-6 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-semibold"
                        >
                            Leave Game
                        </button>
                        @if ($isHost && $game->status === App\GameStatus::Waiting)
                            <button
                                type="button"
                                wire:click="startGame"
                                class="flex-1 py-3 px-6 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors font-semibold"
                                @if(!$game->hasMinimumPlayers()) disabled @endif
                            >
                                Start Game
                            </button>
                        @endif
                    </div>

                    @if ($isHost && !$game->hasMinimumPlayers())
                        <p class="text-center text-sm text-gray-500 mt-3">
                            Waiting for at least 2 players to start the game
                        </p>
                    @endif
                </x-slot:actions>
            </x-multiplayer.lobby-view>
        @endif
    </div>
</div>
