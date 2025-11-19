import game from './game.js';

export default function multiplayerGame(config) {
    // Initialize the base game component
    const instance = game(config);

    // Store original methods WITHOUT binding - we'll call them with proper context
    const originalInit = instance.init;
    const originalRoll = instance.roll;
    const originalUpdateTurnIndicator = instance.updateTurnIndicator;

    // Add multiplayer properties
    instance.isHost = config.isHost;
    instance.gameCode = config.gameCode;
    instance.myPlayerId = config.myPlayerId;
    instance.savedState = config.savedState || null;
    instance.replaying = false;
    instance.isMyTurn = false; // Initialize early so Alpine can track it

    // Override init to setup networking
    instance.init = async function() {
        console.log('MULTIPLAYER init() starting...');
        console.log('Before originalInit, engine is:', this.engine);

        // Call originalInit with the current 'this' context
        await originalInit.call(this);

        console.log('After originalInit, engine is:', this.engine);
        console.log('After originalInit, engine.bus is:', this.engine?.bus);

        // Only setup multiplayer if engine was created successfully
        if (this.engine && this.engine.bus) {
            console.log('Engine initialized successfully, setting up multiplayer...');

            // Restore saved state if it exists
            if (this.savedState) {
                console.log('Restoring saved game state:', this.savedState);
                this.restoreState(this.savedState);
            }

            this.setupMultiplayer();
        } else {
            console.error('Engine not initialized, cannot setup multiplayer');
            console.error('this.engine:', this.engine);
            console.error('this.engine?.bus:', this.engine?.bus);
        }
    };

    instance.setupMultiplayer = function() {
        console.log('Setting up multiplayer for', this.gameCode, 'Host:', this.isHost);

        if (!this.engine || !this.engine.bus) {
            console.error('Engine or bus not available');
            return;
        }

        // Listen for events on the channel
        console.log('Subscribing to channel: game.' + this.gameCode);
        window.Echo.channel(`game.${this.gameCode}`)
            .listen('GameUpdate', (e) => {
                console.log('CLIENT received GameUpdate event:', e);
                if (!this.isHost) {
                    console.log('CLIENT processing GameUpdate:', e.type);
                    this.handleRemoteEvent(e.type, e.payload);
                } else {
                    console.log('CLIENT is host, ignoring GameUpdate');
                }
            })
            .listen('ClientAction', (e) => {
                console.log('HOST received ClientAction event:', e);
                if (this.isHost) {
                    console.log('HOST processing ClientAction:', e.type);
                    this.handleClientAction(e.type, e.payload);
                } else {
                    console.log('Not host, ignoring ClientAction');
                }
            });

        // Intercept local bus events to broadcast them (if Host)
        console.log('Setting up bus listener for local events');
        this.engine.bus.on('*', (type, payload) => {
            this.handleLocalEvent(type, payload);
        });
    };

    instance.handleLocalEvent = function(type, payload) {
        console.log('handleLocalEvent called:', { type, payload, isHost: this.isHost, replaying: this.replaying });

        // Only host broadcasts events, and only if not replaying
        if (this.isHost && !this.replaying) {
            console.log('HOST broadcasting event:', type);
            // Dispatch Livewire event
            window.Livewire.dispatch('game-broadcast', { type, payload });

            // Save game state after turn changes
            if (type === 'TURN_BEGIN') {
                console.log('Turn changed, saving game state...');
                this.saveGameState();
            }
        } else {
            console.log('NOT broadcasting - isHost:', this.isHost, 'replaying:', this.replaying);
        }
    };

    instance.handleRemoteEvent = function(type, payload) {
        // Client receives update from Host
        this.replaying = true;

        // Manually update state where necessary before emitting
        // This mimics what the engine does internally
        if (type === 'MOVE_STEP') {
            const p = this.engine.state.players.find(x => x.id === payload.playerId);
            if (p) p.position = payload.position;
        }
        else if (type === 'TURN_BEGIN') {
            this.engine.state.turnIndex = payload.index;
            this.updateTurnIndicator();
            this.renderTokens();
        }
        else if (type === 'DICE_ROLL') {
            this.engine.state.lastRoll = payload.value;
        }
        else if (type === 'GAME_END') {
            // No state update needed, just toast
        }
        else if (type === 'CARD_DRAWN') {
            // No state update needed, modal handled by listener
        }
        
        // Emit to local bus to trigger UI updates
        this.engine.bus.emit(type, payload);
        
        this.replaying = false;
    };

    instance.handleClientAction = function(type, payload) {
        // Host receives request from Client
        if (type === 'REQUEST_ROLL') {
            // Verify it's the correct player's turn?
            // For now, just trust the request or let the engine handle turn validation
            // But engine.takeTurn() checks skip, etc.
            // We should check if the requesting player is the current player
            const current = this.engine.state.players[this.engine.state.turnIndex];
            // We don't have the requester's ID in the payload currently,
            // but we can assume the client only sends it if it's their turn.
            // Or we can add playerId to payload.

            originalRoll.call(this);
        }
    };

    // Override updateTurnIndicator to show personalized message
    instance.updateTurnIndicator = function() {
        console.log('MULTIPLAYER updateTurnIndicator START', {
            hasEngine: !!this.engine,
            hasState: !!this.engine?.state,
            turnIndex: this.engine?.state?.turnIndex,
            myPlayerId: this.myPlayerId
        });

        if (!this.engine || !this.engine.state) {
            console.error('Cannot update turn indicator - no engine or state');
            return;
        }

        const p = this.engine.state.players[this.engine.state.turnIndex];
        this.isMyTurn = p.id === this.myPlayerId;

        console.log('MULTIPLAYER updateTurnIndicator DATA:', {
            currentPlayer: p,
            currentPlayerId: p.id,
            myPlayerId: this.myPlayerId,
            isMyTurn: this.isMyTurn,
            playerName: p.name
        });

        if (this.isMyTurn) {
            this.currentTurn = "Your turn!";
            console.log('SET currentTurn to: "Your turn!"');
        } else {
            this.currentTurn = `Waiting for ${p.name}...`;
            console.log(`SET currentTurn to: "Waiting for ${p.name}..."`);
        }

        console.log('MULTIPLAYER updateTurnIndicator END, currentTurn is now:', this.currentTurn);
    };

    // Override roll to send request if client
    instance.roll = function() {
        if (this.isHost) {
            originalRoll.call(this);
        } else {
            const current = this.engine.state.players[this.engine.state.turnIndex];
            // Optional: Check if it's my turn before sending
            // if (current.name !== this.myPlayerName) return;

            console.log('CLIENT sending roll request');
            window.Livewire.dispatch('client-action', { type: 'REQUEST_ROLL', payload: {} });
        }
    };

    // Method to save game state to database
    instance.saveGameState = function() {
        if (!this.isHost || !this.engine) {
            return;
        }

        const state = {
            players: this.engine.state.players.map(p => ({
                id: p.id,
                name: p.name,
                color: p.color,
                position: p.position,
                skip: p.skip,
                extraRoll: p.extraRoll
            })),
            turnIndex: this.engine.state.turnIndex,
            lastRoll: this.engine.state.lastRoll,
            decks: this.engine.state.decks
        };

        console.log('Saving game state:', state);
        window.Livewire.find(document.querySelector('[wire\\:id]').getAttribute('wire:id'))
            .call('saveState', state);
    };

    // Method to restore game state
    instance.restoreState = function(state) {
        if (!this.engine || !state) {
            return;
        }

        console.log('Restoring state:', state);

        // Restore player state
        if (state.players) {
            state.players.forEach((savedPlayer, index) => {
                if (this.engine.state.players[index]) {
                    this.engine.state.players[index].position = savedPlayer.position || 0;
                    this.engine.state.players[index].skip = savedPlayer.skip || 0;
                    this.engine.state.players[index].extraRoll = savedPlayer.extraRoll || false;
                }
            });
        }

        // Restore turn index
        if (typeof state.turnIndex === 'number') {
            this.engine.state.turnIndex = state.turnIndex;
        }

        // Restore last roll
        if (typeof state.lastRoll === 'number') {
            this.engine.state.lastRoll = state.lastRoll;
        }

        // Restore decks if they exist
        if (state.decks) {
            this.engine.state.decks = state.decks;
        }

        // Update UI
        this.renderTokens();
        this.updateTurnIndicator();

        console.log('State restored successfully');
    };

    return instance;
}
