<?php

use App\GameStatus;
use App\GameType;
use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;

uses(RefreshDatabase::class);

it('creates a game and redirects to the lobby', function () {
    $component = Volt::test('create-multiplayer-game');

    // Should have created a game
    expect(Game::count())->toBe(1);

    $game = Game::first();

    expect($game->status)->toBe(GameStatus::Waiting)
        ->and($game->game_type)->toBe(GameType::Multiplayer)
        ->and($game->players)->toBe([])
        ->and($game->code)->toBeString()
        ->and(strlen($game->code))->toBe(6)
        ->and($game->code)->toMatch('/^[A-Z0-9]+$/');

    // Should redirect to the lobby with the game code
    $component->assertRedirect('/game/multiplayer/'.$game->code);
});

it('generates unique game codes for multiple games', function () {
    Volt::test('create-multiplayer-game');
    Volt::test('create-multiplayer-game');
    Volt::test('create-multiplayer-game');

    expect(Game::count())->toBe(3);

    $codes = Game::pluck('code')->toArray();

    // All codes should be unique
    expect($codes)->toHaveCount(3)
        ->and(count(array_unique($codes)))->toBe(3);
});
