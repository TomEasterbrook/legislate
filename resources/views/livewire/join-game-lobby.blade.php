<?php

use App\GameStatus;
use App\Models\Game;
use Livewire\Volt\Component;

new class extends Component {
    public string $gameCode = '';

    public function joinGame(): void
    {
        $this->validate([
            'gameCode' => 'required|size:6|alpha_num',
        ], [
            'gameCode.required' => 'Please enter a game code.',
            'gameCode.size' => 'Game code must be exactly 6 characters.',
            'gameCode.alpha_num' => 'Game code must only contain letters and numbers.',
        ]);

        $code = strtoupper($this->gameCode);

        $game = Game::where('code', $code)->first();

        if (! $game) {
            $this->addError('gameCode', 'Game not found. Please check the code and try again.');

            return;
        }

        if ($game->status !== GameStatus::Waiting) {
            $this->addError('gameCode', 'This game has already started or ended.');

            return;
        }

        if ($game->isFull()) {
            $this->addError('gameCode', 'This game is full. Maximum 6 players allowed.');

            return;
        }

        $this->redirect('/game/join/'.$code, navigate: true);
    }

    public function back(): void
    {
        $this->redirect('/');
    }

    public function updated($property): void
    {
        if ($property === 'gameCode') {
            $this->gameCode = strtoupper($this->gameCode);
        }
    }
}; ?>

<div class="flex items-center justify-center px-4 py-12">
    <div class="max-w-md w-full">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-semibold text-gray-900 mb-3">
                Join Game
            </h2>
            <p class="text-lg text-gray-600 mb-8">
                Enter the 6-character game code to join
            </p>

            <div class="bg-white rounded-lg shadow-xl p-8">
                <div class="mb-6">
                    <label for="gameCode" class="block text-sm font-medium text-gray-700 mb-2 text-left">
                        Game Code
                    </label>
                    <input
                        type="text"
                        id="gameCode"
                        wire:model.live="gameCode"
                        wire:keydown.enter="joinGame"
                        placeholder="ABC123"
                        class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500 text-lg text-center uppercase tracking-widest"
                        style="font-family: 'Quintessential', serif;"
                        maxlength="6"
                        autofocus
                    >
                    @error('gameCode')
                        <span class="block text-sm text-red-600 mt-2 text-left">{{ $message }}</span>
                    @enderror
                </div>

                <div class="flex gap-3">
                    <button
                        type="button"
                        wire:click="back"
                        class="flex-1 py-3 px-6 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors font-semibold"
                    >
                        Back
                    </button>
                    <button
                        type="button"
                        wire:click="joinGame"
                        class="flex-1 py-3 px-6 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition-colors font-semibold"
                    >
                        Continue
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
