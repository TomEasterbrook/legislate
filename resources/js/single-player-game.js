// Utility functions
function delay(ms) {
    return new Promise(res => setTimeout(res, ms));
}

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

function dice(rng) {
    return 1 + Math.floor(rng() * 6);
}

// Game Engine
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

// UI Utilities
function showDiceRoll(value) {
    const overlay = document.getElementById('diceOverlay');
    const diceEl = document.getElementById('dice');

    return new Promise((resolve) => {
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
}

function toast(message, { kind = 'info', ttl = 2200 } = {}) {
    let root = document.getElementById('toastRoot');
    if (!root) {
        root = document.createElement('div');
        root.id = 'toastRoot';
        document.body.appendChild(root);
    }

    const el = document.createElement('div');
    el.className = `toast toast--${kind}`;
    el.textContent = message;
    root.appendChild(el);

    setTimeout(() => {
        el.style.transition = 'opacity .25s ease, transform .25s ease';
        el.style.opacity = '0';
        el.style.transform = 'translateY(-4px)';
        setTimeout(() => el.remove(), 300);
    }, ttl);
}

function showModal({ title = '', body = '', actions = null } = {}) {
    return new Promise((resolve) => {
        const root = document.getElementById('modalRoot');
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

        card.appendChild(h);
        card.appendChild(b);
        card.appendChild(acts);
        backdrop.appendChild(card);
        root.appendChild(backdrop);
    });
}

const DECK_LABELS = {
    early: "Early Stages",
    commons: "House of Commons",
    implementation: "Implementation",
    lords: "House of Lords",
    pingpong: "Ping Pong",
};

// Alpine Component
export default function singlePlayerGame(config) {
    return {
        engine: null,
        board: null,
        playerCount: config.playerCount || 4,
        players: [],
        currentTurn: '',
        diceGate: Promise.resolve(),
        diceRunning: false,
        stepQueue: [],
        flushingSteps: false,
        flushDone: Promise.resolve(),

        async init() {
            try {
                const basePath = config.assetPath;

                const [board, commons, early, lords, pingpong, implementation] = await Promise.all([
                    fetch(`${basePath}/board.json`).then(r => r.json()),
                    fetch(`${basePath}/cards/commons.json`).then(r => r.json()),
                    fetch(`${basePath}/cards/early.json`).then(r => r.json()),
                    fetch(`${basePath}/cards/lords.json`).then(r => r.json()),
                    fetch(`${basePath}/cards/pingpong.json`).then(r => r.json()),
                    fetch(`${basePath}/cards/implementation.json`).then(r => r.json()),
                ]);

                this.board = board;
                this.engine = createEngine({
                    board,
                    decks: { commons, early, lords, pingpong, implementation },
                    playerCount: this.playerCount,
                    playerNames: config.playerNames || []
                });

                this.players = this.engine.state.players;
                this.setupEventListeners();
                this.renderTokens();
                this.updateTurnIndicator();
            } catch (err) {
                console.error('BOOT_FAIL', err);
            }
        },

        setupEventListeners() {
            this.engine.bus.on('TURN_BEGIN', ({ index }) => {
                this.updateTurnIndicator();
                this.renderTokens();
            });

            this.engine.bus.on('MOVE_STEP', ({ playerId, position }) => {
                if (this.diceRunning || this.flushingSteps) {
                    this.stepQueue.push({ playerId, position });
                } else {
                    this.applyStep({ playerId, position });
                }
            });

            this.engine.bus.on('DICE_ROLL', () => {
                const v = this.engine.state.lastRoll;
                this.diceRunning = true;
                this.diceGate = showDiceRoll(v)
                    .finally(() => {
                        this.diceRunning = false;
                        this.flushStepQueue();
                    });
            });

            this.engine.bus.on('CARD_DRAWN', async ({ deck, card }) => {
                await Promise.all([this.diceGate, this.flushDone]);

                if (!card) {
                    await showModal({
                        title: 'No card',
                        body: `<p>The ${DECK_LABELS[deck] || deck} deck is empty.</p>`
                    });
                    this.engine.bus.emit('CARD_RESOLVE');
                    return;
                }

                await showModal({
                    title: (card.title || (DECK_LABELS[deck] || deck)),
                    body: `<p>${(card.text || '').trim()}</p>`
                });
                this.engine.bus.emit('CARD_RESOLVE');
            });

            this.engine.bus.on('CARD_APPLIED', ({ card, playerId }) => {
                const p = this.engine.state.players.find(x => x.id === playerId);
                this.renderTokens();

                if (card && typeof card.effect === 'string') {
                    const [type] = card.effect.split(':');
                    if (type === 'extra_roll') {
                        toast(`${p?.name || 'Player'} gets an extra roll`, { kind: 'success' });
                    }
                    if (type === 'miss_turn') {
                        toast(`${p?.name || 'Player'} will miss their next turn`, { kind: 'info' });
                    }
                }
            });

            this.engine.bus.on('MISS_TURN', ({ name }) => {
                toast(`${name} misses a turn`, { kind: 'info' });
            });

            this.engine.bus.on('EFFECT_GOTO', ({ playerId, index }) => {
                const p = this.engine.state.players.find(x => x.id === playerId);
                toast(`${p?.name || 'Player'} jumps to ${index}`, { kind: 'info', ttl: 1800 });
            });

            this.engine.bus.on('GAME_END', ({ name }) => {
                toast(`${name} reached the end!`, { kind: 'success', ttl: 2600 });
            });
        },

        updateTurnIndicator() {
            const p = this.engine.state.players[this.engine.state.turnIndex];
            this.currentTurn = `${p.name}'s turn`;
        },

        positionToken(el, posIndex) {
            const space = this.board.spaces.find(s => s.index === posIndex);
            if (!space) return;
            el.style.left = space.x + '%';
            el.style.top = space.y + '%';
        },

        renderTokens() {
            const layer = document.getElementById('tokensLayer');
            if (!layer) return;

            const groups = new Map();
            this.engine.state.players.forEach(p => {
                const k = String(p.position || 0);
                if (!groups.has(k)) groups.set(k, []);
                groups.get(k).push(p);
            });

            const seen = new Set();
            const TAU = Math.PI * 2;
            const RADIUS_PCT = 2.5;

            for (const [key, group] of groups.entries()) {
                const posIndex = Number(key);
                const space = this.board.spaces.find(s => s.index === posIndex);
                if (!space) continue;

                if (group.length === 1) {
                    const p = group[0];
                    let el = layer.querySelector(`[data-id="${p.id}"]`);
                    if (!el) {
                        el = document.createElement('div');
                        el.className = 'token';
                        el.dataset.id = p.id;
                        layer.appendChild(el);
                    }
                    el.style.background = p.color;
                    el.style.left = space.x + '%';
                    el.style.top = space.y + '%';
                    seen.add(p.id);
                    continue;
                }

                const n = group.length;
                group.forEach((p, i) => {
                    const angle = (i / n) * TAU;
                    const ox = Math.cos(angle) * RADIUS_PCT;
                    const oy = Math.sin(angle) * RADIUS_PCT;
                    let el = layer.querySelector(`[data-id="${p.id}"]`);
                    if (!el) {
                        el = document.createElement('div');
                        el.className = 'token';
                        el.dataset.id = p.id;
                        layer.appendChild(el);
                    }
                    el.style.background = p.color;
                    el.style.left = (space.x + ox) + '%';
                    el.style.top = (space.y + oy) + '%';
                    seen.add(p.id);
                });
            }

            layer.querySelectorAll('.token').forEach(el => {
                const id = el.getAttribute('data-id');
                if (!seen.has(id)) el.remove();
            });
        },

        applyStep({ playerId, position }) {
            const p = this.engine.state.players.find(x => x.id === playerId);
            if (!p) return;

            const layer = document.getElementById('tokensLayer');
            let el = layer.querySelector(`[data-id="${playerId}"]`);
            if (!el) {
                el = document.createElement('div');
                el.className = 'token';
                el.dataset.id = playerId;
                el.style.background = p.color;
                layer.appendChild(el);
            }

            this.positionToken(el, position);
            this.renderTokens();
        },

        flushStepQueue() {
            if (this.flushingSteps) return;

            this.flushingSteps = true;
            let resolveFlush;
            this.flushDone = new Promise(res => resolveFlush = res);

            const tick = () => {
                const item = this.stepQueue.shift();
                if (!item) {
                    this.flushingSteps = false;
                    this.renderTokens();
                    resolveFlush();
                    return;
                }
                this.applyStep(item);
                setTimeout(tick, 180);
            };
            tick();
        },

        roll() {
            this.engine.takeTurn();
        },

        restart() {
            this.engine.reset();
            this.renderTokens();
            this.updateTurnIndicator();
        },

        changePlayerCount(count) {
            this.playerCount = Number(count) || 4;
            this.engine.setPlayerCount(this.playerCount);

            const layer = document.getElementById('tokensLayer');
            layer.innerHTML = '';

            this.players = this.engine.state.players;
            this.renderTokens();
            this.updateTurnIndicator();
        },

        updatePlayerName(index, name) {
            const trimmed = (name || '').trim();
            if (!trimmed) return;
            this.engine.state.players[index].name = trimmed;
            if (index === this.engine.state.turnIndex) {
                this.updateTurnIndicator();
            }
        }
    };
}
