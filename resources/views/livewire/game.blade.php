<?php

use App\Models\Game;
use Livewire\Volt\Component;

new class extends Component
{
    public array $players = [];

    public int $playerCount = 4;

    public ?string $code = null;

    public bool $isMultiplayer = false;

    public function mount(?string $code = null): void
    {
        // Check if this is a multiplayer game (has code parameter from query or route)
        $this->code = $code ?? request()->query('code');

        if ($this->code) {
            // Load multiplayer game
            $game = Game::where('code', strtoupper($this->code))->first();

            if ($game) {
                $this->isMultiplayer = true;
                $this->players = $game->players ?? [];
                $this->playerCount = count($this->players);
            } else {
                // Game not found, redirect home
                $this->redirect('/', navigate: true);
            }
        } else {
            // Local game - get players from session
            $this->players = session('game_players', []);
            $this->playerCount = count($this->players) ?: 4;
        }
    }
}; ?>

<x-slot:title>Play Game - Legislate?!</x-slot:title>
<x-slot:showBack>true</x-slot:showBack>
<x-slot:backUrl>{{ $isMultiplayer ? '/game/multiplayer/' . $code : '/game/local' }}</x-slot:backUrl>
<x-slot:backLabel>Back to {{ $isMultiplayer ? 'Game' : 'Lobby' }}</x-slot:backLabel>

@vite('resources/css/game.css')

<div class="game-container" x-data="{
    ...game({
        playerCount: {{ $playerCount }},
        playerNames: {{ json_encode(array_column($players, 'name')) }},
        assetPath: '{{ asset('game/packs/uk-parliament') }}'
    }),
    panelOpen: false
}">
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
      <!-- Restart Button -->
      <button @click="restart(); panelOpen = false" class="restart-button">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
          <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"/>
          <path d="M21 3v5h-5"/>
          <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"/>
          <path d="M3 21v-5h5"/>
        </svg>
        Restart Game
      </button>

      <!-- Players Section -->
      <div class="player-panel">
        <h3 class="player-panel-title">Players</h3>
        <section class="players-section" aria-label="Players">
          <template x-for="player in players" :key="player.id">
            <div class="player-pill">
              <div class="player-dot" :style="`background: ${player.color}`"></div>
              <span class="player-name" x-text="player.name"></span>
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
    <button @click="roll()" class="dice-button" id="diceButton" aria-label="Roll dice">
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
