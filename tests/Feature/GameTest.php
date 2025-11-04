<?php

use App\GameStatus;
use App\GameType;
use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can create a game', function () {
    $game = Game::factory()->create();

    expect($game)->toBeInstanceOf(Game::class)
        ->and($game->code)->toBeString()
        ->and(strlen($game->code))->toBe(6)
        ->and($game->status)->toBe(GameStatus::Waiting)
        ->and($game->game_type)->toBe(GameType::Multiplayer);
});

it('has a unique game code', function () {
    $game = Game::factory()->create(['code' => 'ABC123']);

    expect($game->code)->toBe('ABC123');
});

it('defaults to waiting status', function () {
    $game = Game::factory()->create();

    expect($game->status)->toBe(GameStatus::Waiting);
});

it('can have different game types', function () {
    $localGame = Game::factory()->local()->create();
    $multiplayerGame = Game::factory()->multiplayer()->create();

    expect($localGame->game_type)->toBe(GameType::Local)
        ->and($multiplayerGame->game_type)->toBe(GameType::Multiplayer);
});

it('can have different statuses', function () {
    $waitingGame = Game::factory()->waiting()->create();
    $inProgressGame = Game::factory()->inProgress()->create();
    $completedGame = Game::factory()->completed()->create();
    $cancelledGame = Game::factory()->cancelled()->create();

    expect($waitingGame->status)->toBe(GameStatus::Waiting)
        ->and($inProgressGame->status)->toBe(GameStatus::InProgress)
        ->and($completedGame->status)->toBe(GameStatus::Completed)
        ->and($cancelledGame->status)->toBe(GameStatus::Cancelled);
});

it('can add a player', function () {
    $game = Game::factory()->create();

    $game->addPlayer('Alice', 'red');

    expect($game->players)->toHaveCount(1)
        ->and($game->players[0]['name'])->toBe('Alice')
        ->and($game->players[0]['color'])->toBe('red');
});

it('can add multiple players', function () {
    $game = Game::factory()->create();

    $game->addPlayer('Alice', 'red');
    $game->addPlayer('Bob', 'blue');
    $game->addPlayer('Charlie', 'green');

    expect($game->players)->toHaveCount(3);
});

it('can remove a player', function () {
    $game = Game::factory()->withPlayers(3)->create();

    expect($game->players)->toHaveCount(3);

    $game->removePlayer(1);

    $game->refresh();

    expect($game->players)->toHaveCount(2)
        ->and($game->players[0]['name'])->not->toBe($game->players[1]['name']);
});

it('reindexes players after removal', function () {
    $game = Game::factory()->create();
    $game->addPlayer('Alice', 'red');
    $game->addPlayer('Bob', 'blue');
    $game->addPlayer('Charlie', 'green');

    $game->removePlayer(1);

    $game->refresh();

    expect(array_keys($game->players))->toBe([0, 1]);
});

it('can get player count', function () {
    $game = Game::factory()->create();

    expect($game->getPlayerCount())->toBe(0);

    $game->addPlayer('Alice', 'red');

    expect($game->getPlayerCount())->toBe(1);

    $game->addPlayer('Bob', 'blue');

    expect($game->getPlayerCount())->toBe(2);
});

it('knows when it is full', function () {
    $game = Game::factory()->withPlayers(5)->create();

    expect($game->isFull())->toBeFalse();

    $game->addPlayer('Frank', 'orange');

    expect($game->isFull())->toBeTrue();
});

it('knows when it has minimum players', function () {
    $game = Game::factory()->create();

    expect($game->hasMinimumPlayers())->toBeFalse();

    $game->addPlayer('Alice', 'red');

    expect($game->hasMinimumPlayers())->toBeFalse();

    $game->addPlayer('Bob', 'blue');

    expect($game->hasMinimumPlayers())->toBeTrue();
});

it('can get used colors', function () {
    $game = Game::factory()->create();
    $game->addPlayer('Alice', 'red');
    $game->addPlayer('Bob', 'blue');
    $game->addPlayer('Charlie', 'green');

    $usedColors = $game->getUsedColors();

    expect($usedColors)->toBe(['red', 'blue', 'green']);
});

it('can be created with players using factory', function () {
    $game = Game::factory()->withPlayers(4)->create();

    expect($game->players)->toHaveCount(4)
        ->and($game->players[0])->toHaveKeys(['name', 'color']);
});

it('casts players to array', function () {
    $game = Game::factory()->create(['players' => [
        ['name' => 'Alice', 'color' => 'red'],
    ]]);

    expect($game->players)->toBeArray();
});

it('casts status to enum', function () {
    $game = Game::factory()->create(['status' => 'waiting']);

    expect($game->status)->toBeInstanceOf(GameStatus::class)
        ->and($game->status)->toBe(GameStatus::Waiting);
});

it('casts game type to enum', function () {
    $game = Game::factory()->create(['game_type' => 'local']);

    expect($game->game_type)->toBeInstanceOf(GameType::class)
        ->and($game->game_type)->toBe(GameType::Local);
});
