<?php

namespace App\Livewire\Concerns;

trait ManagesPlayers
{
    public array $availableColors = [
        'red' => ['name' => 'Red', 'bg' => 'bg-red-500', 'border' => 'border-red-500', 'text' => 'text-red-500'],
        'blue' => ['name' => 'Blue', 'bg' => 'bg-blue-500', 'border' => 'border-blue-500', 'text' => 'text-blue-500'],
        'green' => ['name' => 'Green', 'bg' => 'bg-green-500', 'border' => 'border-green-500', 'text' => 'text-green-500'],
        'yellow' => ['name' => 'Yellow', 'bg' => 'bg-yellow-500', 'border' => 'border-yellow-500', 'text' => 'text-yellow-500'],
        'purple' => ['name' => 'Purple', 'bg' => 'bg-purple-500', 'border' => 'border-purple-500', 'text' => 'text-purple-500'],
        'orange' => ['name' => 'Orange', 'bg' => 'bg-orange-500', 'border' => 'border-orange-500', 'text' => 'text-orange-500'],
    ];

    public function getNextAvailableColor(): string
    {
        $usedColors = array_column($this->players, 'color');
        $availableColorKeys = array_keys($this->availableColors);

        foreach ($availableColorKeys as $color) {
            if (! in_array($color, $usedColors)) {
                return $color;
            }
        }

        return $availableColorKeys[0];
    }

    public function getAvailableColorsForPlayer(int $index): array
    {
        $usedColors = array_column($this->players, 'color');
        $currentPlayerColor = $this->players[$index]['color'];

        return array_filter(
            $this->availableColors,
            fn ($key) => ! in_array($key, $usedColors) || $key === $currentPlayerColor,
            ARRAY_FILTER_USE_KEY
        );
    }

    public function getColorData(string $color): array
    {
        return $this->availableColors[$color] ?? $this->availableColors['red'];
    }

    public function updatePlayerColor(int $index, string $color): void
    {
        if (isset($this->players[$index]) && array_key_exists($color, $this->availableColors)) {
            $this->players[$index]['color'] = $color;
        }
    }

    public function removePlayer(int $index): void
    {
        if (count($this->players) > 2) {
            unset($this->players[$index]);
            $this->players = array_values($this->players);
        }
    }

    public function canAddPlayer(): bool
    {
        return count($this->players) < 6;
    }

    public function canRemovePlayer(): bool
    {
        return count($this->players) > 2;
    }

    public function validatePlayers(): void
    {
        $this->validate([
            'players.*.name' => 'required|min:1|max:50',
            'players.*.color' => 'required|in:'.implode(',', array_keys($this->availableColors)),
        ], [
            'players.*.name.required' => 'All players must have a name.',
            'players.*.name.min' => 'Player names must be at least 1 character.',
            'players.*.name.max' => 'Player names must not exceed 50 characters.',
            'players.*.color.required' => 'All players must have a color.',
            'players.*.color.in' => 'Invalid color selected.',
        ]);
    }
}
