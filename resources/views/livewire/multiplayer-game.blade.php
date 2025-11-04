<?php

use App\GameStatus;
use App\Livewire\Concerns\ManagesPlayers;
use App\Models\Game;
use Livewire\Volt\Component;

new class extends Component {
    use ManagesPlayers;

    public string $gameCode = '';
    public ?Game $game = null;
    public array $players = [];
    public bool $isHost = false;

    public function mount(string $code): void
    {
        $this->gameCode = strtoupper($code);

        $this->game = Game::where('code', $this->gameCode)->first();

        if (! $this->game) {
            $this->redirect('/', navigate: true);

            return;
        }

        $this->players = $this->game->players ?? [];

        // Check if current session is the host (first player)
        // This is simplified - you might want to track this differently
        $this->isHost = count($this->players) > 0;
    }

    public function startGame(): void
    {
        if (! $this->isHost || ! $this->game->hasMinimumPlayers()) {
            return;
        }

        $this->game->status = GameStatus::InProgress;
        $this->game->save();

        // TODO: Redirect to actual game
    }

    public function leaveGame(): void
    {
        $this->redirect('/', navigate: true);
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

<x-multiplayer.lobby-view :gameCode="$gameCode" :players="$players" :subtitle="$this->getSubtitle()">
    @foreach ($players as $index => $player)
        <x-player-card
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
