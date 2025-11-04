<?php

namespace Database\Factories;

use App\GameStatus;
use App\GameType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Game>
 */
class GameFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->bothify('???###')),
            'status' => GameStatus::Waiting,
            'game_type' => GameType::Multiplayer,
            'players' => [],
        ];
    }

    public function withPlayers(int $count = 2): static
    {
        $availableColors = ['red', 'blue', 'green', 'yellow', 'purple', 'orange'];
        $players = [];

        for ($i = 0; $i < min($count, 6); $i++) {
            $players[] = [
                'name' => fake()->firstName(),
                'color' => $availableColors[$i],
            ];
        }

        return $this->state(fn (array $attributes) => [
            'players' => $players,
        ]);
    }

    public function local(): static
    {
        return $this->state(fn (array $attributes) => [
            'game_type' => GameType::Local,
        ]);
    }

    public function multiplayer(): static
    {
        return $this->state(fn (array $attributes) => [
            'game_type' => GameType::Multiplayer,
        ]);
    }

    public function waiting(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => GameStatus::Waiting,
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => GameStatus::InProgress,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => GameStatus::Completed,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => GameStatus::Cancelled,
        ]);
    }
}
