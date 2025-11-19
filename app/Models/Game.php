<?php

namespace App\Models;

use App\GameStatus;
use App\GameType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    /** @use HasFactory<\Database\Factories\GameFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'status',
        'game_type',
        'players',
        'state',
    ];

    protected function casts(): array
    {
        return [
            'status' => GameStatus::class,
            'game_type' => GameType::class,
            'players' => 'array',
            'state' => 'array',
        ];
    }

    public function addPlayer(string $name, string $color): void
    {
        $players = $this->players ?? [];
        $players[] = [
            'name' => $name,
            'color' => $color,
        ];
        $this->players = $players;
        $this->save();
    }

    public function removePlayer(int $index): void
    {
        $players = $this->players ?? [];
        if (isset($players[$index])) {
            unset($players[$index]);
            $this->players = array_values($players);
            $this->save();
        }
    }

    public function getPlayerCount(): int
    {
        return count($this->players ?? []);
    }

    public function isFull(): bool
    {
        return $this->getPlayerCount() >= 6;
    }

    public function hasMinimumPlayers(): bool
    {
        return $this->getPlayerCount() >= 2;
    }

    public function getUsedColors(): array
    {
        return array_column($this->players ?? [], 'color');
    }

    public function saveGameState(array $state): void
    {
        $this->state = $state;
        $this->save();
    }

    public function getGameState(): ?array
    {
        return $this->state;
    }
}
