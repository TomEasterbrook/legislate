<?php

use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;

uses(RefreshDatabase::class);

it('redirects all players to game board when host starts game', function () {
    $game = Game::factory()->create(['code' => 'ABC123']);

    // Create host component and join
    $hostComponent = Volt::test('multiplayer-game', ['code' => 'ABC123']);
    $hostComponent->set('playerName', 'Host')
        ->call('joinGame');

    // Create second player component and join
    $player2Component = Volt::test('multiplayer-game', ['code' => 'ABC123']);
    $player2Component->set('playerName', 'Player Two')
        ->call('joinGame');

    // Refresh host to see player 2
    $hostComponent->call('refreshPlayersJoined');

    // Host starts the game
    $hostComponent->call('startGame')
        ->assertRedirect('/game/play?code=ABC123');

    // Simulate the broadcast event being received by player 2
    $player2Component->call('handleGameStarted')
        ->assertRedirect('/game/play?code=ABC123');
});
