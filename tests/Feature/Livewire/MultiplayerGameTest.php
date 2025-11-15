<?php

use App\GameStatus;
use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;

uses(RefreshDatabase::class);

it('redirects to home when game does not exist', function () {
    $component = Volt::test('multiplayer-game', ['code' => 'NOTFND']);

    $component->assertRedirect('/');
});

it('shows name entry form when player has not joined yet', function () {
    $game = Game::factory()->create(['code' => 'ABC123']);

    $component = Volt::test('multiplayer-game', ['code' => 'ABC123']);

    $component->assertSet('hasJoined', false)
        ->assertSee('ABC123')
        ->assertSee('Game Code')
        ->assertSee('Your Name')
        ->assertSee('Continue')
        ->assertSee('Back');
});

it('shows "Create Multiplayer Game" title when no players exist', function () {
    $game = Game::factory()->create(['code' => 'ABC123']);

    $component = Volt::test('multiplayer-game', ['code' => 'ABC123']);

    $component->assertSee('Create Multiplayer Game')
        ->assertSee('Enter your name to start hosting the game');
});

it('shows "Join Game" title when players already exist', function () {
    $game = Game::factory()->withPlayers(1)->create(['code' => 'ABC123']);

    $component = Volt::test('multiplayer-game', ['code' => 'ABC123']);

    $component->assertSee('Join Game')
        ->assertSee('Enter your name to join the game');
});

it('validates player name is required', function () {
    $game = Game::factory()->create(['code' => 'ABC123']);

    $component = Volt::test('multiplayer-game', ['code' => 'ABC123']);

    $component->call('joinGame')
        ->assertHasErrors(['playerName' => 'required']);
});

it('validates player name max length', function () {
    $game = Game::factory()->create(['code' => 'ABC123']);

    $component = Volt::test('multiplayer-game', ['code' => 'ABC123']);

    $longName = str_repeat('a', 51);

    $component->set('playerName', $longName)
        ->call('joinGame')
        ->assertHasErrors(['playerName' => 'max']);
});

it('can join game as first player (host)', function () {
    $game = Game::factory()->create(['code' => 'ABC123']);

    $component = Volt::test('multiplayer-game', ['code' => 'ABC123']);

    $component->set('playerName', 'Alice')
        ->call('joinGame')
        ->assertHasNoErrors()
        ->assertSet('hasJoined', true)
        ->assertSet('isHost', true)
        ->assertSet('myPlayerName', 'Alice')
        ->assertSet('myPlayerIndex', 0);

    // Verify player was added to the game
    $game->refresh();
    expect($game->players)->toHaveCount(1)
        ->and($game->players[0]['name'])->toBe('Alice');
});

it('can join game as second player', function () {
    $game = Game::factory()->withPlayers(1)->create(['code' => 'ABC123']);

    $component = Volt::test('multiplayer-game', ['code' => 'ABC123']);

    $component->set('playerName', 'Bob')
        ->call('joinGame')
        ->assertHasNoErrors()
        ->assertSet('hasJoined', true)
        ->assertSet('isHost', false)
        ->assertSet('myPlayerName', 'Bob')
        ->assertSet('myPlayerIndex', 1);

    // Verify player was added to the game
    $game->refresh();
    expect($game->players)->toHaveCount(2)
        ->and($game->players[1]['name'])->toBe('Bob');
});

it('shows error when trying to join full game', function () {
    $game = Game::factory()->withPlayers(6)->create(['code' => 'FULL12']);

    $component = Volt::test('multiplayer-game', ['code' => 'FULL12']);

    $component->set('playerName', 'Bob')
        ->call('joinGame')
        ->assertHasErrors(['playerName']);
});

it('shows error when trying to join game that is not waiting', function () {
    $game = Game::factory()->inProgress()->create(['code' => 'GAMEIP']);

    $component = Volt::test('multiplayer-game', ['code' => 'GAMEIP']);

    $component->set('playerName', 'Bob')
        ->call('joinGame')
        ->assertHasErrors(['playerName']);
});

it('always shows name entry form on mount regardless of existing players', function () {
    $game = Game::factory()->create(['code' => 'ABC123']);
    $game->addPlayer('Alice', 'red');
    $game->addPlayer('Bob', 'blue');

    // Even though players exist in the game, user should always be prompted for name
    $component = Volt::test('multiplayer-game', ['code' => 'ABC123']);

    $component->assertSet('hasJoined', false)
        ->assertSee('Join Game')
        ->assertSee('Enter your name to join the game')
        ->assertSee('Your Name');
});

it('host can start game with minimum players', function () {
    $game = Game::factory()->create(['code' => 'ABC123']);

    $component = Volt::test('multiplayer-game', ['code' => 'ABC123']);

    // Join as host
    $component->set('playerName', 'Host')
        ->call('joinGame');

    // Create second player component and join
    $player2 = Volt::test('multiplayer-game', ['code' => 'ABC123']);
    $player2->set('playerName', 'Player2')
        ->call('joinGame');

    // Refresh host component's game state to see the new player
    $component->call('refreshPlayersJoined');

    // Now host should be able to start
    $component->call('startGame')
        ->assertHasNoErrors();

    $game->refresh();
    expect($game->status)->toBe(GameStatus::InProgress);
});

it('non-host cannot start game', function () {
    $game = Game::factory()->create(['code' => 'ABC123']);
    $game->addPlayer('Host', 'red');
    $game->addPlayer('Player2', 'blue');

    $component = Volt::test('multiplayer-game', ['code' => 'ABC123']);

    // Join as third player (non-host)
    $component->set('playerName', 'Player3')
        ->call('joinGame');

    // Refresh to get all players
    $component->call('refreshPlayersJoined');

    // Non-host shouldn't be able to start
    $component->call('startGame');

    $game->refresh();
    expect($game->status)->toBe(GameStatus::Waiting);
});

it('can leave game', function () {
    $game = Game::factory()->create(['code' => 'ABC123']);
    $game->addPlayer('Host', 'red');

    $component = Volt::test('multiplayer-game', ['code' => 'ABC123']);

    // Join as second player
    $component->set('playerName', 'Player2')
        ->call('joinGame');

    expect($game->fresh()->players)->toHaveCount(2);

    // Now leave
    $component->call('leaveGame')
        ->assertRedirect('/');

    $game->refresh();
    expect($game->players)->toHaveCount(1);
});

it('auto-assigns next available color when joining', function () {
    $game = Game::factory()->create(['code' => 'ABC123']);

    $component = Volt::test('multiplayer-game', ['code' => 'ABC123']);

    // First player should get first available color (red)
    $component->assertSet('playerColor', 'red');

    $component->set('playerName', 'Alice')
        ->call('joinGame');

    $game->refresh();
    expect($game->players[0]['color'])->toBe('red');
});

it('updates component state after joining', function () {
    $game = Game::factory()->create(['code' => 'ABC123']);

    $component = Volt::test('multiplayer-game', ['code' => 'ABC123']);

    $component->set('playerName', 'Alice')
        ->call('joinGame')
        ->assertSet('myPlayerName', 'Alice')
        ->assertSet('myPlayerIndex', 0)
        ->assertSet('isHost', true)
        ->assertSet('hasJoined', true);
});
