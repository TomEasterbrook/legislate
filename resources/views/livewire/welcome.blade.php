<?php

use Livewire\Volt\Component;

new class extends Component {
    public function startLocalGame(): void
    {
        // TODO: Navigate to local game
        $this->redirect('/game/local');
    }

    public function startMultiplayerGame(): void
    {
        // TODO: Navigate to multiplayer game creation
        $this->redirect('/game/multiplayer/new');
    }

    public function joinGame(): void
    {
        // TODO: Navigate to join game screen
        $this->redirect('/game/join');
    }
}; ?>

<div class="flex items-center justify-center px-4 py-12">
    <div class="max-w-2xl w-full">
        <!-- Subtitle -->
        <div class="text-center mb-12">
            <h2 class="text-3xl font-semibold text-gray-900 mb-3">
                Choose Your Game Mode
            </h2>
            <p class="text-lg text-gray-600">
                Navigate the UK Parliament in this strategic board game
            </p>
        </div>

        <!-- Game Mode Cards -->
        <div class="space-y-4">
            <x-game-mode-card
                title="Local Game"
                description="Play with friends on the same device"
                color="blue"
                wire-click="startLocalGame"
            />

            <x-game-mode-card
                title="New Multiplayer Game"
                description="Create a new online game and invite players"
                color="indigo"
                wire-click="startMultiplayerGame"
            />

            <x-game-mode-card
                title="Join Game"
                description="Join an existing multiplayer game"
                color="teal"
                wire-click="joinGame"
            />
        </div>
    </div>
</div>
