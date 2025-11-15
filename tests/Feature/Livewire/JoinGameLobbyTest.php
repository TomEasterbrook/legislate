<?php

use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;

uses(RefreshDatabase::class);

it('can render join game lobby page', function () {
    $component = Volt::test('join-game-lobby');

    $component->assertSee('Join Game')
        ->assertSee('Enter the 6-character game code to join')
        ->assertSee('Game Code')
        ->assertSee('Continue')
        ->assertSee('Back');
});

it('starts with empty game code', function () {
    $component = Volt::test('join-game-lobby');

    $component->assertSet('gameCode', '');
});

it('validates game code is required', function () {
    $component = Volt::test('join-game-lobby');

    $component->call('joinGame')
        ->assertHasErrors(['gameCode' => 'required']);
});

it('validates game code must be exactly 6 characters', function () {
    $component = Volt::test('join-game-lobby');

    $component->set('gameCode', 'ABC12')
        ->call('joinGame')
        ->assertHasErrors(['gameCode' => 'size']);

    $component->set('gameCode', 'ABC1234')
        ->call('joinGame')
        ->assertHasErrors(['gameCode' => 'size']);
});

it('validates game code must be alphanumeric', function () {
    $component = Volt::test('join-game-lobby');

    $component->set('gameCode', 'ABC-12')
        ->call('joinGame')
        ->assertHasErrors(['gameCode' => 'alpha_num']);

    $component->set('gameCode', 'ABC 12')
        ->call('joinGame')
        ->assertHasErrors(['gameCode' => 'alpha_num']);
});

it('converts game code to uppercase automatically', function () {
    $component = Volt::test('join-game-lobby');

    $component->set('gameCode', 'abc123');

    expect($component->get('gameCode'))->toBe('ABC123');
});

it('can proceed to join specific game with valid game code', function () {
    $game = Game::factory()->create(['code' => 'ABC123']);

    $component = Volt::test('join-game-lobby');

    $component->set('gameCode', 'ABC123')
        ->call('joinGame')
        ->assertHasNoErrors()
        ->assertRedirect('/game/multiplayer/ABC123');
});

it('redirects to home when back is clicked', function () {
    $component = Volt::test('join-game-lobby');

    $component->call('back')
        ->assertRedirect('/');
});

it('accepts valid alphanumeric game codes', function () {
    Game::factory()->create(['code' => '123456']);
    Game::factory()->create(['code' => 'ABCDEF']);

    $component = Volt::test('join-game-lobby');

    $component->set('gameCode', '123456')
        ->call('joinGame')
        ->assertHasNoErrors()
        ->assertRedirect('/game/multiplayer/123456');

    $component = Volt::test('join-game-lobby');

    $component->set('gameCode', 'ABCDEF')
        ->call('joinGame')
        ->assertHasNoErrors()
        ->assertRedirect('/game/multiplayer/ABCDEF');
});

it('shows error when game code does not exist', function () {
    $component = Volt::test('join-game-lobby');

    $component->set('gameCode', 'NOTFND')
        ->call('joinGame')
        ->assertHasErrors(['gameCode']);
});

it('shows error when game has already started', function () {
    Game::factory()->inProgress()->create(['code' => 'GAMEIP']);

    $component = Volt::test('join-game-lobby');

    $component->set('gameCode', 'GAMEIP')
        ->call('joinGame')
        ->assertHasErrors(['gameCode']);
});

it('shows error when game is full', function () {
    $game = Game::factory()->create(['code' => 'FULL12']);
    $game->players = [
        ['name' => 'Player 1', 'color' => 'red'],
        ['name' => 'Player 2', 'color' => 'blue'],
        ['name' => 'Player 3', 'color' => 'green'],
        ['name' => 'Player 4', 'color' => 'yellow'],
        ['name' => 'Player 5', 'color' => 'purple'],
        ['name' => 'Player 6', 'color' => 'orange'],
    ];
    $game->save();

    $component = Volt::test('join-game-lobby');

    $component->set('gameCode', 'FULL12')
        ->call('joinGame')
        ->assertHasErrors(['gameCode']);
});
