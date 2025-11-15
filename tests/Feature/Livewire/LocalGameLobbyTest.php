<?php

use Livewire\Volt\Volt;

it('can render the lobby', function () {
    $component = Volt::test('local-game-lobby');

    $component->assertSee('Local Game Lobby')
        ->assertSee('Add players to start your local game (2-6 players)')
        ->assertSee('2 / 6 Players')
        ->assertSee('Add Another Player')
        ->assertSee('Start Game')
        ->assertSee('Back');
});

it('starts with two empty players with colors', function () {
    $component = Volt::test('local-game-lobby');

    $component->assertSet('players', [
        ['name' => '', 'color' => 'red'],
        ['name' => '', 'color' => 'blue'],
    ]);
});

it('can add a player with next available color', function () {
    $component = Volt::test('local-game-lobby');

    $component->call('addPlayer')
        ->assertSet('players', [
            ['name' => '', 'color' => 'red'],
            ['name' => '', 'color' => 'blue'],
            ['name' => '', 'color' => 'green'],
        ])
        ->assertSee('3 / 6 Players');
});

it('can add players up to maximum of six', function () {
    $component = Volt::test('local-game-lobby');

    $component->call('addPlayer')
        ->call('addPlayer')
        ->call('addPlayer')
        ->call('addPlayer')
        ->assertSet('players', function ($players) {
            return count($players) === 6;
        })
        ->assertSee('6 / 6 Players')
        ->assertDontSee('Add Another Player');
});

it('cannot add more than six players', function () {
    $component = Volt::test('local-game-lobby');

    // Add 4 more players to reach 6 total
    $component->call('addPlayer')
        ->call('addPlayer')
        ->call('addPlayer')
        ->call('addPlayer');

    // Try to add a 7th player
    $component->call('addPlayer')
        ->assertSet('players', function ($players) {
            return count($players) === 6;
        });
});

it('can remove a player', function () {
    $component = Volt::test('local-game-lobby');

    $component->call('addPlayer')
        ->assertSet('players', function ($players) {
            return count($players) === 3;
        });

    $component->call('removePlayer', 2)
        ->assertSet('players', function ($players) {
            return count($players) === 2;
        })
        ->assertSee('2 / 6 Players');
});

it('cannot remove players when only two remain', function () {
    $component = Volt::test('local-game-lobby');

    $component->call('removePlayer', 1)
        ->assertSet('players', function ($players) {
            return count($players) === 2;
        });
});

it('can update player names', function () {
    $component = Volt::test('local-game-lobby');

    $component->set('players.0.name', 'Alice')
        ->set('players.1.name', 'Bob')
        ->assertSet('players', [
            ['name' => 'Alice', 'color' => 'red'],
            ['name' => 'Bob', 'color' => 'blue'],
        ]);
});

it('validates that all players must have names when starting game', function () {
    $component = Volt::test('local-game-lobby');

    $component->set('players.0.name', 'Alice')
        ->set('players.1.name', '')
        ->call('startGame')
        ->assertHasErrors(['players.1.name' => 'required']);
});

it('validates player name maximum length', function () {
    $component = Volt::test('local-game-lobby');

    $longName = str_repeat('a', 51);

    $component->set('players.0.name', $longName)
        ->set('players.1.name', 'Bob')
        ->call('startGame')
        ->assertHasErrors(['players.0.name' => 'max']);
});

it('can start game with valid player names', function () {
    $component = Volt::test('local-game-lobby');

    $component->set('players.0.name', 'Alice')
        ->set('players.1.name', 'Bob')
        ->call('startGame')
        ->assertHasNoErrors()
        ->assertRedirect('/game/play');
});

it('can navigate back to welcome page', function () {
    $component = Volt::test('local-game-lobby');

    $component->call('back')
        ->assertRedirect('/');
});

it('shows correct player count as players are added', function () {
    $component = Volt::test('local-game-lobby');

    $component->assertSee('2 / 6 Players');

    $component->call('addPlayer')
        ->assertSee('3 / 6 Players');

    $component->call('addPlayer')
        ->assertSee('4 / 6 Players');
});

it('re-indexes players after removal', function () {
    $component = Volt::test('local-game-lobby');

    $component->call('addPlayer')
        ->set('players.0.name', 'Alice')
        ->set('players.1.name', 'Bob')
        ->set('players.2.name', 'Charlie');

    $component->call('removePlayer', 1)
        ->assertSet('players', function ($players) {
            return count($players) === 2
                && $players[0]['name'] === 'Alice'
                && $players[1]['name'] === 'Charlie';
        });
});

it('can change player colors', function () {
    $component = Volt::test('local-game-lobby');

    $component->set('players.0.color', 'green')
        ->assertSet('players.0.color', 'green');
});

it('assigns unique colors when adding players', function () {
    $component = Volt::test('local-game-lobby');

    $component->call('addPlayer')
        ->call('addPlayer')
        ->call('addPlayer')
        ->call('addPlayer');

    $players = $component->get('players');
    $colors = array_column($players, 'color');

    expect($colors)->toHaveCount(6);
    expect(array_unique($colors))->toHaveCount(6);
});

it('validates color is required when starting game', function () {
    $component = Volt::test('local-game-lobby');

    $component->set('players.0.name', 'Alice')
        ->set('players.1.name', 'Bob')
        ->set('players.0.color', '')
        ->call('startGame')
        ->assertHasErrors(['players.0.color']);
});

it('validates color must be valid option', function () {
    $component = Volt::test('local-game-lobby');

    $component->set('players.0.name', 'Alice')
        ->set('players.1.name', 'Bob')
        ->set('players.0.color', 'invalid-color')
        ->call('startGame')
        ->assertHasErrors(['players.0.color']);
});

it('shows all six distinct colors', function () {
    $component = Volt::test('local-game-lobby');

    $availableColors = $component->get('availableColors');

    expect($availableColors)->toHaveCount(6);
    expect($availableColors)->toHaveKeys(['red', 'blue', 'green', 'yellow', 'purple', 'orange']);
});
