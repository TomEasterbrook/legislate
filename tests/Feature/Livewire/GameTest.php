<?php

use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;

uses(RefreshDatabase::class);

it('can render', function () {
    $component = Volt::test('game');

    $component->assertSee('');
});

it('loads multiplayer game data when code parameter is provided', function () {
    $game = Game::factory()->create(['code' => 'ABC123']);
    $game->addPlayer('Alice', 'red');
    $game->addPlayer('Bob', 'blue');
    $game->addPlayer('Charlie', 'green');

    // Visit the page with the code as a query parameter
    $response = $this->get('/game/play?code=ABC123');

    $response->assertSuccessful()
        ->assertSee('Alice')
        ->assertSee('Bob')
        ->assertSee('Charlie');
});

it('loads local game data from session when no code parameter', function () {
    session(['game_players' => [
        ['name' => 'Player 1', 'color' => 'red'],
        ['name' => 'Player 2', 'color' => 'blue'],
    ]]);

    $component = Volt::test('game');

    $component->assertSet('isMultiplayer', false)
        ->assertSet('code', null)
        ->assertSet('playerCount', 2);

    expect($component->get('players'))->toHaveCount(2);
});

it('redirects to home when multiplayer game code not found', function () {
    $response = $this->get('/game/play?code=NOTFOUND');

    $response->assertRedirect('/');
});

it('uses default player count when no session data for local game', function () {
    $component = Volt::test('game');

    $component->assertSet('isMultiplayer', false)
        ->assertSet('playerCount', 4);
});

it('shows correct back button URL for multiplayer game', function () {
    $game = Game::factory()->create(['code' => 'ABC123']);
    $game->addPlayer('Alice', 'red');

    $response = $this->get('/game/play?code=ABC123');

    $response->assertSuccessful()
        ->assertSee('/game/multiplayer/ABC123')
        ->assertSee('Back to Game');
});

it('shows correct back button URL for local game', function () {
    session(['game_players' => [
        ['name' => 'Player 1', 'color' => 'red'],
    ]]);

    $response = $this->get('/game/play');

    $response->assertSuccessful()
        ->assertSee('/game/local')
        ->assertSee('Back to Lobby');
});
