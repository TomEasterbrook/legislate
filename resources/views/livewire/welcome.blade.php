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
                Learn how legislation moves through the UK Parliament
            </p>
        </div>

        <!-- Game Mode Cards -->
        <div class="space-y-4">
            <x-game-mode-card
                title="Local Game"
                description="Practice the legislative process with colleagues on a shared device, perfect for team training sessions"
                color="blue"
                wire-click="startLocalGame"
            >
                <x-slot:icon>
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </x-slot:icon>
            </x-game-mode-card>

            <x-game-mode-card
                title="New Multiplayer Game"
                description="Start a collaborative learning session and invite colleagues to explore parliamentary procedures together online"
                color="indigo"
                wire-click="startMultiplayerGame"
            >
                <x-slot:icon>
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"/>
                    </svg>
                </x-slot:icon>
            </x-game-mode-card>

            <x-game-mode-card
                title="Join Game"
                description="Join a training session in progress and learn alongside other civil servants about how bills become law"
                color="teal"
                wire-click="joinGame"
            >
                <x-slot:icon>
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </x-slot:icon>
            </x-game-mode-card>
        </div>
    </div>
</div>
