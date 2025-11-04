<?php

use App\GameStatus;
use App\Livewire\Concerns\ManagesPlayers;
use App\Models\Game;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new class extends Component {
    use ManagesPlayers;

    public string $gameCode = '';
    public string $playerName = '';
    public string $playerColor = 'red';
    public array $players = [];
    public ?int $gameId = null;

    public function mount(string $code): void
    {
        $this->gameCode = strtoupper($code);

        $game = Game::where('code', $this->gameCode)->first();

        if (! $game) {
            $this->redirect('/game/join', navigate: true);

            return;
        }

        if ($game->status !== GameStatus::Waiting) {
            $this->redirect('/game/join', navigate: true);

            return;
        }

        if ($game->isFull()) {
            $this->redirect('/game/join', navigate: true);

            return;
        }

        $this->gameId = $game->id;
        $this->players = $game->players ?? [];
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

        $game = Game::find($this->gameId);

        if (! $game || $game->status !== GameStatus::Waiting || $game->isFull()) {
            $this->addError('playerName', 'Unable to join game. Please try again.');

            return;
        }

        $playerIndex = $game->getPlayerCount();
        $game->addPlayer($this->playerName, $this->playerColor);

        // Store player info in session
        session(['game_'.$this->gameCode.'_player_name' => $this->playerName]);
        session(['game_'.$this->gameCode.'_player_index' => $playerIndex]);

        \App\Events\PlayerJoinedGame::dispatch($game, $this->playerName, $this->playerColor);

        $this->redirect('/game/multiplayer/'.$this->gameCode, navigate: true);
    }

    public function back(): void
    {
        $this->redirect('/game/join', navigate: true);
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
    public function handlePlayerLeft(): void
    {
        $game = Game::find($this->gameId);
        if ($game) {
            $this->players = $game->fresh()->players ?? [];

            // If host left (no players), redirect to join page
            if (count($this->players) === 0) {
                $this->redirect('/game/join', navigate: true);
            }
        }
    }

    #[On('echo:game.{gameCode},GameStarted')]
    public function handleGameStarted(): void
    {
        $this->redirect('/game/multiplayer/'.$this->gameCode, navigate: true);
    }
}; ?>

<div class="flex items-center justify-center px-4 py-12">
    <div class="max-w-4xl w-full">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-semibold text-gray-900 mb-3">
                Join Game
            </h2>

            <!-- Game Code Display -->
            <div class="inline-block bg-white rounded-lg shadow-lg px-4 py-3 border-2 border-gray-200 mb-6">
                <p class="text-xs font-medium text-gray-500 mb-1">Game Code</p>
                <p class="text-3xl font-bold text-gray-900 tracking-wide" style="font-family: 'Quintessential', serif;">{{ $gameCode }}</p>
            </div>

            <p class="text-lg text-gray-600 mb-8">
                Enter your name to join the game
            </p>

            <div class="bg-white rounded-lg shadow-xl p-8 max-w-md mx-auto">
                <!-- Player Name Input -->
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
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-lg"
                        maxlength="50"
                        autofocus
                    >
                    @error('playerName')
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
                        wire:click="joinGame"
                        class="flex-1 py-3 px-6 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition-colors font-semibold"
                    >
                        Join Game
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
