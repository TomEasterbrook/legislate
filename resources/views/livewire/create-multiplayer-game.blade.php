<?php

use App\GameStatus;
use App\GameType;
use App\Models\Game;
use Livewire\Volt\Component;

new class extends Component {
    public function mount(): void
    {
        // Generate game code
        $gameCode = strtoupper(substr(md5(uniqid()), 0, 6));

        // Create game
        Game::create([
            'code' => $gameCode,
            'status' => GameStatus::Waiting,
            'game_type' => GameType::Multiplayer,
            'players' => [],
        ]);

        // Redirect to the unified lobby
        $this->redirect('/game/multiplayer/'.$gameCode, navigate: true);
    }
}; ?>

<div>
    <!-- This component redirects immediately on mount -->
</div>
