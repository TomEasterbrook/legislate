<?php

use Livewire\Volt\Volt;

Volt::route('/', 'welcome');
Volt::route('/game/local', 'local-game-lobby');
Volt::route('/game/play', 'single-player-game');
Volt::route('/game/multiplayer/new', 'create-multiplayer-game');
Volt::route('/game/multiplayer/{code}', 'multiplayer-game');
Volt::route('/game/join', 'join-game-lobby');
