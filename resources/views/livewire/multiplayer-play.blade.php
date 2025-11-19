<?php

use App\Models\Game;
use Livewire\Volt\Component;
use Livewire\Attributes\On;

new class extends Component
{
    public array $players = [];
    public int $playerCount = 4;
    public ?string $code = null;
    public bool $isHost = false;
    public ?string $myPlayerId = null;
    public ?array $savedState = null;

    public function mount(?string $code = null): void
    {
        $this->code = $code ?? request()->query('code');

        if (!$this->code) {
            $this->redirect('/', navigate: true);
            return;
        }

        $game = Game::where('code', strtoupper($this->code))->first();

        if (!$game) {
            $this->redirect('/', navigate: true);
            return;
        }

        $this->players = $game->players ?? [];
        $this->playerCount = count($this->players);
        $this->savedState = $game->getGameState();

        $myName = session('multiplayer_player_name');
        if ($myName) {
            foreach ($this->players as $index => $player) {
                if ($player['name'] === $myName) {
                    $this->myPlayerId = 'p' . ($index + 1);
                    $this->isHost = ($index === 0);
                    break;
                }
            }
        }
    }

    #[On('game-broadcast')]
    public function broadcastEvent($type = null, $payload = []): void
    {
        \Log::debug('broadcastEvent called', ['type' => $type, 'payload' => $payload, 'isHost' => $this->isHost, 'code' => $this->code]);

        if ($this->isHost && $type) {
            \Log::debug('Dispatching GameUpdate event', ['code' => $this->code, 'type' => $type]);
            \App\Events\GameUpdate::dispatch($this->code, $type, $payload);
        }
    }

    #[On('client-action')]
    public function clientAction($type = null, $payload = []): void
    {
        \Log::debug('clientAction called', ['type' => $type, 'payload' => $payload, 'code' => $this->code]);

        if ($type) {
            \App\Events\ClientAction::dispatch($this->code, $type, $payload);
        }
    }

    public function saveState(array $state): void
    {
        if (!$this->isHost) {
            return;
        }

        $game = Game::where('code', strtoupper($this->code))->first();
        if ($game) {
            $game->saveGameState($state);
            \Log::debug('Game state saved', ['code' => $this->code, 'state' => $state]);
        }
    }
}; ?>

<x-slot:title>Play Multiplayer - Legislate?!</x-slot:title>
<x-slot:showBack>true</x-slot:showBack>
<x-slot:backUrl>/game/multiplayer/{{ $code }}</x-slot:backUrl>
<x-slot:backLabel>Back to Lobby</x-slot:backLabel>

@vite('resources/css/game.css')

<div class="game-container" x-data="{
    ...multiplayerGame({
        playerCount: {{ $playerCount }},
        playerNames: {{ json_encode(array_column($players, 'name')) }},
        assetPath: '{{ asset('game/packs/uk-parliament') }}',
        isHost: {{ $isHost ? 'true' : 'false' }},
        gameCode: '{{ $code }}',
        myPlayerId: '{{ $myPlayerId ?? '' }}',
        savedState: {{ $savedState ? json_encode($savedState) : 'null' }}
    }),
    panelOpen: false
}"
x-init="
    console.log('Game initialized with myPlayerId:', myPlayerId, 'isHost:', isHost);
    $watch('isMyTurn', value => console.log('isMyTurn changed to:', value));
    $watch('currentTurn', value => console.log('currentTurn changed to:', value));
"
>
  <!-- Toggle Panel Button -->
  <button @click="panelOpen = !panelOpen" class="panel-toggle" aria-label="Toggle panel">
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <line x1="3" y1="12" x2="21" y2="12"/>
      <line x1="3" y1="6" x2="21" y2="6"/>
      <line x1="3" y1="18" x2="21" y2="18"/>
    </svg>
  </button>

  <!-- Collapsible Side Panel -->
  <div class="side-panel" :class="{ 'side-panel--open': panelOpen }">
    <div class="side-panel-header">
      <h2 class="side-panel-title">Game Menu</h2>
      <button @click="panelOpen = false" class="panel-close" aria-label="Close panel">
        <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <line x1="18" y1="6" x2="6" y2="18"/>
          <line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
      </button>
    </div>

    <div class="side-panel-content">
      <!-- Restart Button (Host Only) -->
      <template x-if="isHost">
          <button @click="restart(); panelOpen = false" class="restart-button">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
              <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/>
              <path d="M21 3v5h-5"/>
              <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/>
              <path d="M3 21v-5h5"/>
            </svg>
            Restart Game
          </button>
      </template>

      <!-- Players Section -->
      <div class="player-panel">
        <h3 class="player-panel-title">Players</h3>
        <section class="players-section" aria-label="Players">
          <template x-for="player in players" :key="player.id">
            <div class="player-pill">
              <div class="player-dot" :style="`background: ${player.color}`"></div>
              <span class="player-name" x-text="player.name"></span>
              <template x-if="player.id === myPlayerId">
                  <span class="text-xs text-gray-500 ml-2">(You)</span>
              </template>
            </div>
          </template>
        </section>
      </div>
    </div>
  </div>

  <!-- Backdrop -->
  <div class="panel-backdrop" x-show="panelOpen" @click="panelOpen = false" x-transition:enter="transition-opacity" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

  <!-- Board Section -->
  <div class="board-section">
    <!-- Dice Button -->
    <button @click="roll()" class="dice-button" id="diceButton" aria-label="Roll dice" :disabled="!isMyTurn">
      <div class="dice-wrapper">
        <div id="dice" class="dice">
          <div class="face one"></div>
          <div class="face two"></div>
          <div class="face three"></div>
          <div class="face four"></div>
          <div class="face five"></div>
          <div class="face six"></div>
        </div>
      </div>
      <div class="dice-turn-indicator" x-text="currentTurn">Loading…</div>
    </button>

    <div class="board-wrap">
      <img id="boardImg" class="board-img" src="{{ asset('game/packs/uk-parliament/board.png') }}" alt="UK Parliament board">
      <div id="tokensLayer" class="tokens-layer" aria-live="polite"></div>
    </div>
  </div>

  <div id="modalRoot"></div>

  <p class="attrib">
    Created by Dan Easterbrook. Inspired by the "Legislate?!" board game created by Hayley Rogers. Utilises assets created by Terence Eden, licensed under the Open Government License.
  </p>
</div>
