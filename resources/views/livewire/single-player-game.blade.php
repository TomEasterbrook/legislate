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

@assets
<style>
@import url('https://fonts.googleapis.com/css2?family=Quintessential&display=swap');

:root {
  --bg: #ffffff;
  --text: #0b0c0c;
  --muted: #505a5f;
  --link: #1d70b8;
  --border: #d8dadd;
  --panel: #f7f8f9;
  --accent: #1d70b8;
  --shadow-soft: 0 1px 1px rgba(0,0,0,.05), 0 6px 16px rgba(0,0,0,.08);
}

.game-container {
  max-width: 1100px;
  margin: 0 auto;
  padding: 1rem 1.25rem;
}

.game-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
  flex-wrap: wrap;
  border-bottom: 1px solid var(--border);
  background: #fff;
  padding: 1rem 0;
  margin-bottom: 1rem;
}

.brand {
  font-family: 'Quintessential', serif;
  font-size: clamp(1.4rem, 2vw + 1rem, 2rem);
  letter-spacing: .5px;
  font-weight: 700;
}

.control-bar {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 1.5rem;
}

.control-box {
  background: var(--panel);
  padding: 1rem;
  border-radius: 0.5rem;
  border: 1px solid var(--border);
}

.control-heading {
  font-size: 1rem;
  font-weight: 600;
  margin: 0 0 0.75rem 0;
}

.button-row {
  display: flex;
  gap: 0.5rem;
}

.button {
  appearance: none;
  border: 1px solid var(--border);
  background: #fff;
  padding: .48rem .85rem;
  border-radius: .5rem;
  font-weight: 600;
  cursor: pointer;
  transition: all .2s ease;
  font-family: inherit;
}

.button:hover {
  background: var(--panel);
}

.button:active {
  transform: translateY(1px);
}

.button--secondary {
  background: #fff;
}

.turn {
  margin: .25rem 0 0;
  font-weight: 700;
  font-size: 1.1rem;
}

.players-section {
  display: flex;
  flex-wrap: wrap;
  gap: 0.5rem;
  margin-top: 0.75rem;
}

.player-pill {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  background: #fff;
  padding: 0.4rem 0.75rem;
  border-radius: 1rem;
  border: 1px solid var(--border);
}

.player-dot {
  width: 12px;
  height: 12px;
  border-radius: 50%;
  box-shadow: 0 1px 2px rgba(0,0,0,.2);
}

.player-name {
  font-size: 0.9rem;
  font-weight: 600;
  outline: none;
  border: none;
  background: transparent;
}

.player-name:focus {
  outline: 2px solid var(--accent);
  outline-offset: 2px;
  border-radius: 2px;
}

.board-wrap {
  position: relative;
  user-select: none;
  margin-bottom: 1rem;
}

.board-img {
  display: block;
  width: 100%;
  object-fit: contain;
  border-radius: .25rem;
  background: #fff;
  box-shadow: 0 6px 15px rgba(0,0,0,.15);
}

.tokens-layer {
  position: absolute;
  inset: 0;
  pointer-events: none;
}

.token {
  position: absolute;
  width: 20px;
  height: 20px;
  border-radius: 50%;
  box-shadow: 0 1px 2px rgba(0,0,0,.2);
  transform: translate(-50%, -50%);
  transition: left 0.3s ease, top 0.3s ease;
}

.dice-overlay {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,.7);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
}

.dice-overlay[hidden] {
  display: none;
}

.dice-wrapper {
  perspective: 1000px;
}

.dice {
  width: 100px;
  height: 100px;
  position: relative;
  transform-style: preserve-3d;
  transition: transform 0.6s;
}

.dice.rolling {
  animation: roll 0.6s ease-out;
}

@keyframes roll {
  0% { transform: rotateX(0deg) rotateY(0deg); }
  50% { transform: rotateX(360deg) rotateY(180deg); }
  100% { transform: rotateX(720deg) rotateY(360deg); }
}

.face {
  position: absolute;
  width: 100px;
  height: 100px;
  background: white;
  border: 2px solid #333;
  border-radius: 10px;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 3rem;
  font-weight: bold;
}

.dice.show-1 .one,
.dice.show-2 .two,
.dice.show-3 .three,
.dice.show-4 .four,
.dice.show-5 .five,
.dice.show-6 .six {
  transform: translateZ(50px);
}

.one::before { content: '⚀'; }
.two::before { content: '⚁'; }
.three::before { content: '⚂'; }
.four::before { content: '⚃'; }
.five::before { content: '⚄'; }
.six::before { content: '⚅'; }

.modal-backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,.6);
  display: none;
  align-items: center;
  justify-content: center;
  z-index: 999;
  padding: 1rem;
}

.modal {
  background: white;
  padding: 2rem;
  border-radius: 1rem;
  max-width: 500px;
  width: 100%;
  box-shadow: 0 10px 40px rgba(0,0,0,.3);
}

.modal h2 {
  margin: 0 0 1rem 0;
  font-size: 1.5rem;
  color: var(--text);
}

.modal-body {
  margin-bottom: 1.5rem;
  line-height: 1.6;
}

.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 0.75rem;
}

.button--primary {
  background: var(--accent);
  color: #fff;
  border-color: transparent;
}

.button--primary:hover {
  filter: brightness(0.96);
}

#toastRoot {
  position: fixed;
  right: 12px;
  top: 12px;
  z-index: 2000;
  display: flex;
  flex-direction: column;
  gap: 8px;
}

.toast {
  padding: 10px 12px;
  color: #fff;
  border-radius: 8px;
  box-shadow: 0 6px 16px rgba(0,0,0,.15);
  font-weight: 600;
  max-width: 320px;
  word-break: break-word;
}

.toast--info { background: #1d70b8; }
.toast--success { background: #00703c; }
.toast--error { background: #d4351c; }

select {
  font-family: inherit;
  padding: 0.5rem;
  border: 1px solid var(--border);
  border-radius: 0.375rem;
  background: white;
}

label {
  display: block;
  margin-bottom: 0.25rem;
  font-weight: 600;
  font-size: 0.9rem;
}

.attrib {
  font-size: 0.75rem;
  color: var(--muted);
  text-align: center;
  margin-top: 2rem;
}
</style>
@endassets

<div class="game-container">
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
        <button id="rollBtn" class="button">Roll</button>
        <button id="restartBtn" class="button button--secondary">Restart</button>
      </div>
    </div>
    <div class="control-box">
      <div id="turnIndicator" class="turn">Loading…</div>
    </div>
    <div class="control-box">
      <label for="playerCount">Players</label>
      <select id="playerCount" aria-label="Number of players">
        <option value="2">2</option>
        <option value="3">3</option>
        <option value="4" {{ $playerCount == 4 ? 'selected' : '' }}>4</option>
        <option value="5">5</option>
        <option value="6">6</option>
      </select>
      <section id="playersSection" class="players-section" aria-label="Players"></section>
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

@script
<script>
// Game Engine
window.LegislateEngine = (function () {
  function delay(ms) { return new Promise(res => setTimeout(res, ms)); }
  function createEventBus() {
    const map = new Map();
    return {
      on(type, fn) {
        if (!map.has(type)) map.set(type, new Set());
        map.get(type).add(fn);
        return () => map.get(type)?.delete(fn);
      },
      emit(type, payload) {
        (map.get(type) || []).forEach(fn => fn(payload));
        (map.get('*') || []).forEach(fn => fn(type, payload));
      },
    };
  }
  function makeRng(seed) {
    let t = seed >>> 0;
    return function () {
      t += 0x6D2B79F5;
      let r = Math.imul(t ^ (t >>> 15), 1 | t);
      r ^= r + Math.imul(r ^ (r >>> 7), 61 | r);
      return ((r ^ (r >>> 14)) >>> 0) / 4294967296;
    };
  }
  function dice(rng) { return 1 + Math.floor(rng() * 6); }

  function createEngine({ board, decks, rng = makeRng(Date.now()), playerCount = 4, colors, playerNames = [] } = {}) {
    const bus = createEventBus();
    const state = { players: [], turnIndex: 0, decks: {}, lastRoll: 0 };
    const endIndex = (board.spaces.slice().reverse().find(s => s.stage === 'end') || board.spaces[board.spaces.length - 1]).index;

    const palette = colors || ['#d4351c', '#1d70b8', '#00703c', '#6f72af', '#b58840', '#912b88'];
    function initPlayers(n) {
      const max = Math.max(2, Math.min(6, n || 4));
      state.players = [];
      for (let i = 0; i < max; i++) {
        state.players.push({
          id: 'p' + (i + 1),
          name: playerNames[i] || 'Player ' + (i + 1),
          color: palette[i % palette.length],
          position: 0,
          skip: 0,
          extraRoll: false,
        });
      }
      state.turnIndex = 0;
    }
    initPlayers(playerCount);

    for (const [name, cards] of Object.entries(decks || {})) {
      state.decks[name] = cards.slice();
    }

    function current() { return state.players[state.turnIndex]; }
    function spaceFor(i) { return board.spaces.find(s => s.index === i) || null; }
    function drawFrom(name) {
      const d = state.decks[name];
      if (!d || !d.length) return null;
      const c = d.shift();
      return c;
    }

    function applyCard(card) {
      if (!card) return;

      if (typeof card.effect === 'string' && card.effect.length) {
        const [type, arg] = card.effect.split(':');

        if (type === 'move') {
          const n = Number(arg || 0);
          const p = current();
          let i = p.position + n;
          if (i < 0) i = 0;
          if (i > endIndex) i = endIndex;
          p.position = i;

        } else if (type === 'miss_turn') {
          current().skip = (current().skip || 0) + 1;

        } else if (type === 'extra_roll') {
          current().extraRoll = true;

        } else if (type === 'goto') {
          const p = current();
          let i = Number(arg || 0);
          if (i < 0) i = 0;
          if (i > endIndex) i = endIndex;
          p.position = i;
          bus.emit('EFFECT_GOTO', { playerId: p.id, index: i });
        }
      }
    }

    async function moveSteps(n) {
      const p = current();
      const step = n >= 0 ? 1 : -1;
      const count = Math.abs(n);
      for (let k = 0; k < count; k++) {
        p.position += step;
        if (p.position < 0) p.position = 0;
        if (p.position > endIndex) p.position = endIndex;
        bus.emit('MOVE_STEP', { playerId: p.id, position: p.position, step: k + 1, total: count });
        await delay(180);
      }
    }

    function checkWin(p) {
      if (p.position >= endIndex) {
        bus.emit('GAME_END', { playerId: p.id, name: p.name, position: p.position });
        return true;
      }
      return false;
    }

    async function takeTurn() {
      const p = current();
      if (p.skip > 0) { p.skip--; endTurn(false); return; }

      const roll = dice(rng);
      state.lastRoll = roll;
      bus.emit('DICE_ROLL', { value: roll, playerId: p.id, name: p.name });

      await moveSteps(roll);

      if (checkWin(p)) return;

      const space = spaceFor(p.position);
      bus.emit('LANDED', { playerId: p.id, position: p.position, space });

      let card = null;
      if (space && space.deck && space.deck !== 'none') {
        const d = state.decks[space.deck] || [];
        bus.emit('DECK_CHECK', { name: space.deck, len: d.length });
        card = drawFrom(space.deck);
        bus.emit('CARD_DRAWN', { deck: space.deck, card });
        if (card) {
          await new Promise(res => {
            const off = bus.on('CARD_RESOLVE', () => { off(); res(); });
          });
          applyCard(card);
          bus.emit('CARD_APPLIED', { card, playerId: p.id, position: p.position });

          if (checkWin(p)) return;
        }
      }

      endTurn(p.extraRoll);
      p.extraRoll = false;
    }

    function nextEligibleTurnIndex() {
      const skipped = [];
      const max = state.players.length || 0;
      let hops = 0;
      let idx = state.turnIndex;

      while (hops < max) {
        idx = (idx + 1) % state.players.length;
        const p = state.players[idx];

        if (p.skip > 0) {
          p.skip -= 1;
          skipped.push({ playerId: p.id, name: p.name, remaining: p.skip });
          hops += 1;
          continue;
        }
        return { index: idx, skipped };
      }
      return { index: idx, skipped };
    }

    function endTurn(extra) {
      if (extra === true) {
        bus.emit('TURN_BEGIN', { playerId: current().id, index: state.turnIndex });
        return;
      }
      const sel = nextEligibleTurnIndex();
      state.turnIndex = sel.index;

      if (sel.skipped && sel.skipped.length) {
        for (let i = 0; i < sel.skipped.length; i++) {
          bus.emit('MISS_TURN', sel.skipped[i]);
        }
      }
      bus.emit('TURN_BEGIN', { playerId: current().id, index: state.turnIndex });
    }

    function setPlayerCount(n) {
      const names = state.players.map(p => p.name);
      initPlayers(n);
      state.players.forEach((p, i) => { if (names[i]) p.name = names[i]; });
      bus.emit('TURN_BEGIN', { playerId: current().id, index: state.turnIndex });
    }

    function reset() {
      state.players.forEach(p => { p.position = 0; p.skip = 0; p.extraRoll = false; });
      state.turnIndex = 0;
      for (const [name, cards] of Object.entries(decks || {})) { state.decks[name] = cards.slice(); }
      bus.emit('TURN_BEGIN', { playerId: current().id, index: state.turnIndex });
    }

    return { bus, state, endIndex, takeTurn, setPlayerCount, reset };
  }

  return { createEngine, makeRng };
})();

// UI Module
window.LegislateUI = (function () {
  const $ = (id) => document.getElementById(id);

  function setTurnIndicator(text) {
    const el = $('turnIndicator');
    if (el) el.textContent = text;
  }

  function createBoardRenderer(arg) {
    const layer = $('tokensLayer');
    if (!layer) return { render: () => {} };
    const board = (arg && arg.board) ? arg.board : arg;

    const coordsFor = (index) => {
      const space = board && board.spaces && board.spaces.find(s => s.index === index);
      return {
        x: space && typeof space.x === 'number' ? space.x : 0,
        y: space && typeof space.y === 'number' ? space.y : 0
      };
    };

    function ensureToken(id, color) {
      let el = layer.querySelector(`[data-id="${id}"]`);
      if (!el) {
        el = document.createElement('div');
        el.className = 'token';
        el.dataset.id = id;
        el.style.background = color;
        layer.appendChild(el);
      } else {
        el.style.background = color;
      }
      return el;
    }

    function render(players) {
      if (!Array.isArray(players)) return;

      const groups = new Map();
      players.forEach(p => {
        const k = String(p.position || 0);
        if (!groups.has(k)) groups.set(k, []);
        groups.get(k).push(p);
      });

      const seen = new Set();
      const TAU = Math.PI * 2;
      const RADIUS_PCT = 2.5;

      for (const [key, group] of groups.entries()) {
        const posIndex = Number(key);
        const { x, y } = coordsFor(posIndex);

        if (group.length === 1) {
          const p = group[0];
          const t = ensureToken(p.id, p.color);
          t.style.left = x + '%';
          t.style.top  = y + '%';
          seen.add(p.id);
          continue;
        }

        const n = group.length;
        group.forEach((p, i) => {
          const angle = (i / n) * TAU;
          const ox = Math.cos(angle) * RADIUS_PCT;
          const oy = Math.sin(angle) * RADIUS_PCT;
          const t = ensureToken(p.id, p.color);
          t.style.left = (x + ox) + '%';
          t.style.top  = (y + oy) + '%';
          seen.add(p.id);
        });
      }

      layer.querySelectorAll('.token').forEach(el => {
        const id = el.getAttribute('data-id');
        if (!seen.has(id)) el.remove();
      });
    }

    return { render };
  }

  function createModal() {
    const root = $('modalRoot');
    function open({ title = '', body = '', actions } = {}) {
      return new Promise((resolve) => {
        if (!root) return resolve();

        root.innerHTML = '';
        const backdrop = document.createElement('div');
        backdrop.className = 'modal-backdrop';
        backdrop.style.display = 'flex';

        const card = document.createElement('div');
        card.className = 'modal';

        const h = document.createElement('h2');
        h.textContent = title || 'Card';

        const b = document.createElement('div');
        b.className = 'modal-body';
        b.innerHTML = body;

        const acts = document.createElement('div');
        acts.className = 'modal-actions';

        const list = actions && actions.length ? actions : [{ label: 'OK', value: true }];
        list.forEach(a => {
          const btn = document.createElement('button');
          btn.className = 'button button--primary';
          btn.textContent = a.label || 'OK';
          btn.addEventListener('click', () => { root.innerHTML = ''; resolve(a.value); });
          acts.appendChild(btn);
        });

        card.appendChild(h); card.appendChild(b); card.appendChild(acts);
        backdrop.appendChild(card);
        root.appendChild(backdrop);
      });
    }
    return { open };
  }

  let __diceDone__ = Promise.resolve();

  function showDiceRoll(value) {
    const overlay = $('diceOverlay');
    const diceEl  = $('dice');

    __diceDone__ = new Promise((resolve) => {
      if (!overlay || !diceEl) { resolve(); return; }

      overlay.hidden = false;
      diceEl.className = 'dice rolling';

      setTimeout(() => {
        const v = Math.max(1, Math.min(6, Number(value) || 1));
        diceEl.className = 'dice show-' + v + ' rolling';
      }, 300);

      setTimeout(() => {
        overlay.hidden = true;
        diceEl.className = 'dice';
        resolve();
      }, 2500);
    });

    return __diceDone__;
  }

  function waitForDice(){ return __diceDone__; }

  (function ensureToast(){
    if (document.getElementById('toastRoot')) return;
    const root = document.createElement('div');
    root.id = 'toastRoot';
    Object.assign(root.style, {
      position:'fixed', right:'12px', top:'12px', zIndex:'2000',
      display:'flex', flexDirection:'column', gap:'8px'
    });
    document.body.appendChild(root);
  })();

  function toast(message, { kind='info', ttl=2200 } = {}) {
    const root = document.getElementById('toastRoot');
    const el = document.createElement('div');
    el.className = `toast toast--${kind}`;
    Object.assign(el.style, {
      padding:'10px 12px',
      background: kind === 'error' ? '#d4351c' : (kind === 'success' ? '#00703c' : '#1d70b8'),
      color:'#fff', borderRadius:'8px', boxShadow:'0 6px 16px rgba(0,0,0,.15)',
      fontWeight:'600', maxWidth:'320px', wordBreak:'break-word'
    });
    el.textContent = message;
    root.appendChild(el);
    setTimeout(() => {
      el.style.transition = 'opacity .25s ease, transform .25s ease';
      el.style.opacity = '0';
      el.style.transform = 'translateY(-4px)';
      setTimeout(() => el.remove(), 300);
    }, ttl);
  }

  return {
    setTurnIndicator,
    createBoardRenderer,
    createModal,
    showDiceRoll,
    waitForDice,
    toast
  };
})();

// App Initialization
(async function boot() {
  const $ = (id) => document.getElementById(id);

  try {
    const basePath = '{{ asset("game/packs/uk-parliament") }}';

    const [board, commons, early, lords, pingpong, implementation] = await Promise.all([
      fetch(`${basePath}/board.json`).then(r => r.json()),
      fetch(`${basePath}/cards/commons.json`).then(r => r.json()),
      fetch(`${basePath}/cards/early.json`).then(r => r.json()),
      fetch(`${basePath}/cards/lords.json`).then(r => r.json()),
      fetch(`${basePath}/cards/pingpong.json`).then(r => r.json()),
      fetch(`${basePath}/cards/implementation.json`).then(r => r.json()),
    ]);

    const playerNames = @json($players).map(p => p.name || '').filter(n => n);
    const engine = window.LegislateEngine.createEngine({
      board,
      decks: { commons, early, lords, pingpong, implementation },
      playerCount: {{ $playerCount }},
      playerNames
    });

    window.engine = engine;
    window.board = board;

    const tokensLayer = $('tokensLayer');
    const tokenEls = new Map();

    function ensureToken(id, color) {
      if (tokenEls.has(id)) return tokenEls.get(id);
      const el = document.createElement('div');
      el.className = 'token';
      el.style.background = color;
      el.dataset.id = id;
      tokensLayer.appendChild(el);
      tokenEls.set(id, el);
      return el;
    }

    function positionToken(el, posIndex) {
      const space = board.spaces.find(s => s.index === posIndex);
      if (!space) return;
      el.style.left = space.x + '%';
      el.style.top = space.y + '%';
    }

    engine.state.players.forEach(p => {
      const el = ensureToken(p.id, p.color);
      positionToken(el, p.position);
    });

    $('rollBtn').addEventListener('click', () => engine.takeTurn());
    $('restartBtn').addEventListener('click', () => { engine.reset(); renderPlayers(); });
    $('playerCount').addEventListener('change', (e) => {
      engine.setPlayerCount(Number(e.target.value) || 4);
      tokensLayer.innerHTML = '';
      tokenEls.clear();
      engine.state.players.forEach(p => {
        const el = ensureToken(p.id, p.color);
        positionToken(el, p.position);
      });
      renderPlayers();
    });

    function renderPlayers() {
      const root = $('playersSection');
      root.innerHTML = '';
      engine.state.players.forEach((p, i) => {
        const pill = document.createElement('div');
        pill.className = 'player-pill';

        const dot = document.createElement('div');
        dot.className = 'player-dot';
        dot.style.background = p.color;

        const name = document.createElement('span');
        name.className = 'player-name';
        name.contentEditable = 'true';
        name.textContent = p.name;

        function applyName() {
          const v = (name.textContent || '').trim();
          if (!v) return;
          engine.state.players[i].name = v;
          if (i === engine.state.turnIndex) {
            $('turnIndicator').textContent = `${v}'s turn`;
          }
        }
        name.addEventListener('input', applyName);
        name.addEventListener('blur', applyName);

        pill.appendChild(dot);
        pill.appendChild(name);
        root.appendChild(pill);
      });
    }
    renderPlayers();

    const boardUI = window.LegislateUI.createBoardRenderer({ board });

    let diceGate = Promise.resolve();
    let diceRunning = false;
    let stepQueue = [];
    let flushingSteps = false;
    let flushDone = Promise.resolve();

    function applyStep({ playerId, position }) {
      const p = engine.state.players.find(x => x.id === playerId);
      if (!p) return;
      const el = ensureToken(playerId, p.color);
      positionToken(el, position);
      boardUI.render(engine.state.players);
    }

    function flushStepQueue() {
      if (flushingSteps) return;

      flushingSteps = true;
      let resolveFlush;
      flushDone = new Promise(res => resolveFlush = res);

      const tick = () => {
        const item = stepQueue.shift();
        if (!item) {
          flushingSteps = false;
          boardUI.render(engine.state.players);
          resolveFlush();
          return;
        }
        applyStep(item);
        setTimeout(tick, 180);
      };
      tick();
    }

    engine.bus.on('TURN_BEGIN', ({ index }) => {
      const p = engine.state.players[index];
      $('turnIndicator').textContent = `${p.name}'s turn`;

      engine.state.players.forEach(pl => {
        const el = ensureToken(pl.id, pl.color);
        positionToken(el, pl.position);
      });

      boardUI.render(engine.state.players);
    });

    engine.bus.on('MOVE_STEP', ({ playerId, position }) => {
      if (diceRunning || flushingSteps) {
        stepQueue.push({ playerId, position });
      } else {
        applyStep({ playerId, position });
      }
    });

    engine.bus.on('DICE_ROLL', () => {
      const v = engine.state.lastRoll;
      diceRunning = true;
      diceGate = window.LegislateUI.showDiceRoll(v)
        .finally(() => {
          diceRunning = false;
          flushStepQueue();
        });
    });

    const DECK_LABELS = {
      early: "Early Stages",
      commons: "House of Commons",
      implementation: "Implementation",
      lords: "House of Lords",
      pingpong: "Ping Pong",
    };

    engine.bus.on('CARD_DRAWN', async ({ deck, card }) => {
      await Promise.all([diceGate, flushDone]);

      const modal = window.LegislateUI.createModal();

      if (!card) {
        await modal.open({ title: 'No card', body: `<p>The ${DECK_LABELS[deck] || deck} deck is empty.</p>` });
        engine.bus.emit('CARD_RESOLVE');
        return;
      }

      await modal.open({
        title: (card.title || (DECK_LABELS[deck] || deck)),
        body: `<p>${(card.text || '').trim()}</p>`
      });
      engine.bus.emit('CARD_RESOLVE');
    });

    engine.bus.on('CARD_APPLIED', ({ card, playerId }) => {
      const p = engine.state.players.find(x => x.id === playerId);
      const el = ensureToken(playerId, p.color);
      positionToken(el, p.position);
      boardUI.render(engine.state.players);

      if (card && typeof card.effect === 'string') {
        const [type] = card.effect.split(':');
        if (type === 'extra_roll') {
          window.LegislateUI.toast(`${p?.name || 'Player'} gets an extra roll`, { kind: 'success' });
        }
        if (type === 'miss_turn') {
          window.LegislateUI.toast(`${p?.name || 'Player'} will miss their next turn`, { kind: 'info' });
        }
      }
    });

    engine.bus.on('MISS_TURN', ({ name }) => {
      window.LegislateUI.toast(`${name} misses a turn`, { kind: 'info' });
    });

    engine.bus.on('EFFECT_GOTO', ({ playerId, index }) => {
      const p = engine.state.players.find(x => x.id === playerId);
      window.LegislateUI.toast(`${p?.name || 'Player'} jumps to ${index}`, { kind: 'info', ttl: 1800 });
    });

    engine.bus.on('GAME_END', ({ name }) => {
      window.LegislateUI.toast(`${name} reached the end!`, { kind: 'success', ttl: 2600 });
    });

    engine.bus.emit('TURN_BEGIN', { index: engine.state.turnIndex, playerId: engine.state.players[engine.state.turnIndex].id });

  } catch (err) {
    console.error('BOOT_FAIL', err);
  }
})();
</script>
@endscript
