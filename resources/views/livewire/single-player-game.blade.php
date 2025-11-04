<?php

use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new class extends Component {
    public array $players = [];
    public int $playerCount = 4;

    public function mount(): void
    {
        // Get players from session or use defaults
        $this->players = session('game_players', []);
        $this->playerCount = count($this->players) ?: 4;
    }

    public function back(): void
    {
        $this->redirect('/game/local', navigate: true);
    }
}; ?>

@vite('resources/css/single-player-game.css')

<div class="game-container" x-data="singlePlayerGame({
    playerCount: {{ $playerCount }},
    playerNames: {{ json_encode(array_column($players, 'name')) }},
    assetPath: '{{ asset('game/packs/uk-parliament') }}'
})">
  <!-- Header -->
  <div class="game-header">
    <div class="brand">Legislate?!</div>
    <button wire:click="back" class="button button--secondary">Back to Lobby</button>
  </div>

  <!-- Control Bar -->
  <div class="control-bar">
    <div class="control-box">
      <h2 class="control-heading">Controls</h2>
      <div class="button-row">
        <button @click="roll()" class="button">Roll</button>
        <button @click="restart()" class="button button--secondary">Restart</button>
      </div>
    </div>
    <div class="control-box">
      <div x-text="currentTurn" class="turn">Loading…</div>
    </div>
    <div class="control-box">
      <label for="playerCount">Players</label>
      <select @change="changePlayerCount($event.target.value)" x-model="playerCount" id="playerCount" aria-label="Number of players">
        <option value="2">2</option>
        <option value="3">3</option>
        <option value="4">4</option>
        <option value="5">5</option>
        <option value="6">6</option>
      </select>
      <section class="players-section" aria-label="Players">
        <template x-for="(player, index) in players" :key="player.id">
          <div class="player-pill">
            <div class="player-dot" :style="`background: ${player.color}`"></div>
            <span
              class="player-name"
              contenteditable="true"
              x-text="player.name"
              @blur="updatePlayerName(index, $event.target.textContent)"
              @input="updatePlayerName(index, $event.target.textContent)"></span>
          </div>
        </template>
      </section>
    </div>
  </div>

  <!-- Main Content -->
  <div class="board-wrap">
    <img id="boardImg" class="board-img" src="{{ asset('game/packs/uk-parliament/board.png') }}" alt="UK Parliament board">
    <div id="tokensLayer" class="tokens-layer" aria-live="polite"></div>
  </div>

  <div id="modalRoot"></div>

  <div id="diceOverlay" class="dice-overlay" hidden>
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
  </div>

  <p class="attrib">
    Created by Dan Easterbrook. Inspired by the "Legislate?!" board game created by Hayley Rogers. Utilises assets created by Terence Eden, licensed under the Open Government License.
  </p>
</div>
