<?php

use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;

uses(RefreshDatabase::class);

it('can render join specific game page', function () {
    Game::factory()->create(['code' => 'ABC123']);

    $component = Volt::test('join-specific-game', ['code' => 'ABC123']);

    $component->assertSee('Join Game')
        ->assertSee('ABC123')
        ->assertSee('Enter your name to join the game')
        ->assertSee('Your Name')
        ->assertSee('Join Game')
        ->assertSee('Back');
});

it('sets game code from route parameter in uppercase', function () {
    Game::factory()->create(['code' => 'ABC123']);

    $component = Volt::test('join-specific-game', ['code' => 'abc123']);

    $component->assertSet('gameCode', 'ABC123');
});

it('auto-assigns color on mount', function () {
    Game::factory()->create(['code' => 'ABC123']);

    $component = Volt::test('join-specific-game', ['code' => 'ABC123']);

    $playerColor = $component->get('playerColor');

    expect($playerColor)->toBeString()
        ->and(in_array($playerColor, ['red', 'blue', 'green', 'yellow', 'purple', 'orange']))->toBeTrue();
});

it('validates player name is required', function () {
    Game::factory()->create(['code' => 'ABC123']);

    $component = Volt::test('join-specific-game', ['code' => 'ABC123']);

    $component->call('joinGame')
        ->assertHasErrors(['playerName' => 'required']);
});

it('validates player name max length', function () {
    Game::factory()->create(['code' => 'ABC123']);

    $component = Volt::test('join-specific-game', ['code' => 'ABC123']);

    $longName = str_repeat('a', 51);

    $component->set('playerName', $longName)
        ->call('joinGame')
        ->assertHasErrors(['playerName' => 'max']);
});

it('can join game with valid name', function () {
    Game::factory()->create(['code' => 'ABC123']);

    $component = Volt::test('join-specific-game', ['code' => 'ABC123']);

    $component->set('playerName', 'Bob')
        ->call('joinGame')
        ->assertHasNoErrors()
        ->assertRedirect('/game/multiplayer/ABC123');
});

it('redirects to join lobby when back is clicked', function () {
    Game::factory()->create(['code' => 'ABC123']);

    $component = Volt::test('join-specific-game', ['code' => 'ABC123']);

    $component->call('back')
        ->assertRedirect('/game/join');
});

it('displays game code prominently', function () {
    Game::factory()->create(['code' => 'XYZ789']);

    $component = Volt::test('join-specific-game', ['code' => 'XYZ789']);

    $component->assertSee('XYZ789')
        ->assertSee('Game Code');
});

it('assigns first available color when no players exist', function () {
    Game::factory()->create(['code' => 'ABC123']);

    $component = Volt::test('join-specific-game', ['code' => 'ABC123']);

    // When no players exist, should get first color (red)
    $component->assertSet('playerColor', 'red');
});

it('redirects when game does not exist', function () {
    $component = Volt::test('join-specific-game', ['code' => 'NOTFND']);

    $component->assertRedirect('/game/join');
});

it('redirects when game has already started', function () {
    Game::factory()->inProgress()->create(['code' => 'GAMEIP']);

    $component = Volt::test('join-specific-game', ['code' => 'GAMEIP']);

    $component->assertRedirect('/game/join');
});

it('redirects when game is full', function () {
    $game = Game::factory()->withPlayers(6)->create(['code' => 'FULL12']);

    $component = Volt::test('join-specific-game', ['code' => 'FULL12']);

    $component->assertRedirect('/game/join');
});
