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
            <!-- Local Game -->
            <button
                wire:click="startLocalGame"
                class="w-full bg-white rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 p-8 text-left group border-2 border-gray-200 hover:border-blue-500"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-semibold text-gray-900 mb-2">
                            Local Game
                        </h2>
                        <p class="text-gray-600">
                            Play with friends on the same device
                        </p>
                    </div>
                    <svg class="w-8 h-8 text-blue-600 transform group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </div>
            </button>

            <!-- New Multiplayer Game -->
            <button
                wire:click="startMultiplayerGame"
                class="w-full bg-white rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 p-8 text-left group border-2 border-gray-200 hover:border-indigo-500"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-semibold text-gray-900 mb-2">
                            New Multiplayer Game
                        </h2>
                        <p class="text-gray-600">
                            Create a new online game and invite players
                        </p>
                    </div>
                    <svg class="w-8 h-8 text-indigo-600 transform group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </div>
            </button>

            <!-- Join Game -->
            <button
                wire:click="joinGame"
                class="w-full bg-white rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 p-8 text-left group border-2 border-gray-200 hover:border-teal-500"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-2xl font-semibold text-gray-900 mb-2">
                            Join Game
                        </h2>
                        <p class="text-gray-600">
                            Join an existing multiplayer game
                        </p>
                    </div>
                    <svg class="w-8 h-8 text-teal-600 transform group-hover:translate-x-2 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </div>
            </button>
        </div>
    </div>
</div>
