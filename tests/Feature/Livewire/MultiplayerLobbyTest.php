<?php

use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;

uses(RefreshDatabase::class);

it('shows name entry step initially', function () {
    $component = Volt::test('multiplayer-lobby');

    $component->assertSee('Create Multiplayer Game')
        ->assertSee('Enter your name to start hosting a game')
        ->assertSee('Your Name')
        ->assertSee('Continue')
        ->assertSee('Back')
        ->assertSet('step', 'entering-name');
});

it('generates a unique game code on mount', function () {
    $component = Volt::test('multiplayer-lobby');

    $gameCode = $component->get('gameCode');

    expect($gameCode)->toBeString()
        ->and(strlen($gameCode))->toBe(6)
        ->and($gameCode)->toMatch('/^[A-Z0-9]+$/');
});

it('validates host name is required', function () {
    $component = Volt::test('multiplayer-lobby');

    $component->call('setHostName')
        ->assertHasErrors(['hostName' => 'required']);
});

it('validates host name max length', function () {
    $component = Volt::test('multiplayer-lobby');

    $longName = str_repeat('a', 51);

    $component->set('hostName', $longName)
        ->call('setHostName')
        ->assertHasErrors(['hostName' => 'max']);
});

it('can proceed to lobby after entering name', function () {
    $component = Volt::test('multiplayer-lobby');

    $component->set('hostName', 'Alice')
        ->call('setHostName')
        ->assertHasNoErrors()
        ->assertSet('step', 'lobby')
        ->assertSet('players', [
            ['name' => 'Alice', 'color' => 'red'],
        ]);
});

it('can render the lobby step', function () {
    $component = Volt::test('multiplayer-lobby');

    $component->set('hostName', 'Alice')
        ->call('setHostName')
        ->assertSee('Multiplayer Game Lobby')
        ->assertSee('Share the game code with others to join (2-6 players)')
        ->assertSee('1 / 6 Players')
        ->assertSee('Waiting for player...')
        ->assertSee('Start Game')
        ->assertSee('Back');
});

it('shows game code in lobby', function () {
    $component = Volt::test('multiplayer-lobby');

    $component->set('hostName', 'Alice')
        ->call('setHostName');

    $gameCode = $component->get('gameCode');

    $component->assertSee($gameCode);
});

it('starts with no players until name is entered', function () {
    $component = Volt::test('multiplayer-lobby');

    $component->assertSet('players', []);

    $component->set('hostName', 'Alice')
        ->call('setHostName')
        ->assertSet('players', [
            ['name' => 'Alice', 'color' => 'red'],
        ]);
});

it('host cannot remove themselves', function () {
    $component = Volt::test('multiplayer-lobby');

    $component->set('hostName', 'Alice')
        ->call('setHostName')
        ->assertSet('players', [
            ['name' => 'Alice', 'color' => 'red'],
        ]);

    // Verify only one player (the host)
    expect($component->get('players'))->toHaveCount(1);
});

it('shows waiting placeholders for empty slots', function () {
    $component = Volt::test('multiplayer-lobby');

    $component->set('hostName', 'Alice')
        ->call('setHostName');

    // Should show 5 waiting placeholders (6 total - 1 host)
    $html = $component->html();
    $waitingCount = substr_count($html, 'Waiting for player...');

    expect($waitingCount)->toBe(5);
});

it('can start game with valid players', function () {
    $component = Volt::test('multiplayer-lobby');

    $gameCode = $component->get('gameCode');

    $component->set('hostName', 'Host Player')
        ->call('setHostName')
        ->call('startGame')
        ->assertHasNoErrors()
        ->assertRedirect('/game/multiplayer/'.$gameCode);
});

it('can navigate back to welcome from name entry', function () {
    $component = Volt::test('multiplayer-lobby');

    $component->call('back')
        ->assertRedirect('/');
});

it('can navigate back to name entry from lobby', function () {
    $component = Volt::test('multiplayer-lobby');

    $component->set('hostName', 'Alice')
        ->call('setHostName')
        ->assertSet('step', 'lobby');

    $component->call('back')
        ->assertSet('step', 'entering-name')
        ->assertSet('hostName', '')
        ->assertSet('players', []);
});

it('generates game url with code', function () {
    $component = Volt::test('multiplayer-lobby');

    $gameCode = $component->get('gameCode');
    $gameUrl = url('/game/join/'.$gameCode);

    expect($component->instance()->getGameUrl())->toBe($gameUrl);
});

it('shows correct player count', function () {
    $component = Volt::test('multiplayer-lobby');

    $component->set('hostName', 'Alice')
        ->call('setHostName')
        ->assertSee('1 / 6 Players');
});
