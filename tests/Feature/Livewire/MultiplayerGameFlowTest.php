<?php

use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;

uses(RefreshDatabase::class);

it('complete flow: host creates game and second player joins', function () {
    // Step 1: Host creates a new game
    $hostComponent = Volt::test('create-multiplayer-game');

    $game = Game::first();
    expect($game)->not->toBeNull();

    $gameCode = $game->code;

    // Step 2: Host arrives at the lobby (should see name entry form)
    $hostComponent = Volt::test('multiplayer-game', ['code' => $gameCode]);

    $hostComponent->assertSet('hasJoined', false)
        ->assertSet('players', [])
        ->assertSee('Create Multiplayer Game')
        ->assertSee('Enter your name to start hosting the game');

    // Step 3: Host enters their name
    $hostComponent->set('playerName', 'Host Player')
        ->call('joinGame')
        ->assertHasNoErrors();

    // Step 4: Verify host is now in the lobby
    $hostComponent->assertSet('hasJoined', true)
        ->assertSet('myPlayerName', 'Host Player')
        ->assertSet('myPlayerIndex', 0)
        ->assertSet('isHost', true)
        ->assertSee('Multiplayer Game Lobby');

    // Verify host sees themselves in players list
    expect($hostComponent->get('players'))->toHaveCount(1)
        ->and($hostComponent->get('players')[0]['name'])->toBe('Host Player');

    // Step 5: Second player visits the same game (different component instance)
    $player2Component = Volt::test('multiplayer-game', ['code' => $gameCode]);

    // Step 6: Second player should see name entry form
    $player2Component->assertSet('hasJoined', false)
        ->assertSee('Join Game')
        ->assertSee('Enter your name to join the game');

    // Verify they see the existing host in the players array
    expect($player2Component->get('players'))->toHaveCount(1);

    // Step 7: Second player enters their name
    $player2Component->set('playerName', 'Player Two')
        ->call('joinGame')
        ->assertHasNoErrors();

    // Step 8: Verify second player is now in the lobby
    $player2Component->assertSet('hasJoined', true)
        ->assertSet('myPlayerName', 'Player Two')
        ->assertSet('myPlayerIndex', 1)
        ->assertSet('isHost', false)
        ->assertSee('Multiplayer Game Lobby');

    // Verify second player sees both players
    expect($player2Component->get('players'))->toHaveCount(2)
        ->and($player2Component->get('players')[0]['name'])->toBe('Host Player')
        ->and($player2Component->get('players')[1]['name'])->toBe('Player Two');

    // Step 9: Refresh the game and verify both players are in the database
    $game->refresh();
    expect($game->players)->toHaveCount(2)
        ->and($game->players[0]['name'])->toBe('Host Player')
        ->and($game->players[1]['name'])->toBe('Player Two');
});
