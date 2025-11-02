<?php

use Livewire\Volt\Volt;

Volt::route('/', 'welcome');
Volt::route('/game/local', 'local-game-lobby');
Volt::route('/game/multiplayer/new', 'multiplayer-lobby');
