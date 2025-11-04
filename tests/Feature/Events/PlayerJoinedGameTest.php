<?php

declare(strict_types=1);

use App\Events\PlayerJoinedGame;
use App\GameStatus;
use App\GameType;
use App\Models\Game;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;

uses(RefreshDatabase::class);
uses()->group('events');

test('player joined game event broadcasts on correct channel', function () {
    Event::fake();

    $game = Game::factory()->create([
        'code' => 'ABC123',
        'status' => GameStatus::Waiting,
        'game_type' => GameType::Multiplayer,
        'players' => [
            ['name' => 'Alice', 'color' => 'red'],
        ],
    ]);

    $event = new PlayerJoinedGame($game, 'Bob', 'blue');

    expect($event->broadcastOn())->toHaveCount(1);
    expect($event->broadcastOn()[0]->name)->toBe('game.ABC123');
});

test('player joined game event includes correct data', function () {
    $game = Game::factory()->create([
        'code' => 'ABC123',
        'status' => GameStatus::Waiting,
        'game_type' => GameType::Multiplayer,
        'players' => [
            ['name' => 'Alice', 'color' => 'red'],
        ],
    ]);

    $event = new PlayerJoinedGame($game, 'Bob', 'blue');
    $data = $event->broadcastWith();

    expect($data)->toHaveKeys(['players', 'playerCount']);
    expect($data['players'])->toBeArray();
    expect($data['playerCount'])->toBe(1);
});
