<?php

use Livewire\Volt\Component;

new class extends Component
{
    public array $board = [];
    public ?int $selectedSpace = null;
    public string $boardPath = 'game/packs/uk-parliament/board.json';
    public string $imagePath = 'game/packs/uk-parliament/board.png';

    public function mount(): void
    {
        $this->loadBoard();
    }

    public function loadBoard(): void
    {
        $jsonPath = public_path($this->boardPath);
        if (file_exists($jsonPath)) {
            $this->board = json_decode(file_get_contents($jsonPath), true);
        } else {
            $this->board = [
                'asset' => 'assets/board.png',
                'spaces' => []
            ];
        }
    }

    public function addSpace(float $x, float $y): void
    {
        $newIndex = count($this->board['spaces']);

        $this->board['spaces'][] = [
            'index' => $newIndex,
            'x' => round($x, 2),
            'y' => round($y, 2),
            'stage' => 'early',
            'deck' => 'none'
        ];

        $this->selectedSpace = $newIndex;
    }

    public function updateSpace(int $index, string $field, string $value): void
    {
        if (isset($this->board['spaces'][$index])) {
            if ($field === 'x' || $field === 'y') {
                $this->board['spaces'][$index][$field] = (float) $value;
            } else {
                $this->board['spaces'][$index][$field] = $value;
            }
        }
    }

    public function moveSpace(int $index, float $x, float $y): void
    {
        if (isset($this->board['spaces'][$index])) {
            $this->board['spaces'][$index]['x'] = round($x, 2);
            $this->board['spaces'][$index]['y'] = round($y, 2);
        }
    }

    public function deleteSpace(int $index): void
    {
        if (isset($this->board['spaces'][$index])) {
            array_splice($this->board['spaces'], $index, 1);

            // Reindex all spaces
            foreach ($this->board['spaces'] as $i => $space) {
                $this->board['spaces'][$i]['index'] = $i;
            }

            $this->selectedSpace = null;
        }
    }

    public function selectSpace(int $index): void
    {
        $this->selectedSpace = $index;
    }

    public function save(): void
    {
        $jsonPath = public_path($this->boardPath);

        file_put_contents(
            $jsonPath,
            json_encode($this->board, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        $this->dispatch('board-saved');
    }
}; ?>

<x-slot:title>Board Editor - Legislate?!</x-slot:title>

<div class="min-h-screen bg-gray-100 dark:bg-gray-900 py-8">
    <div class="mx-auto px-4 max-w-7xl">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Board Editor</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    Click on the board to add spaces. Drag spaces to move them. Click spaces to edit properties.
                </p>
            </div>
            <div class="flex gap-2">
                <button
                    wire:click="loadBoard"
                    class="rounded-lg bg-gray-600 px-4 py-2 text-white hover:bg-gray-700"
                >
                    Reset
                </button>
                <button
                    wire:click="save"
                    class="rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700"
                >
                    Save Board
                </button>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-3">
            <!-- Board Display -->
            <div class="lg:col-span-2">
                <div class="overflow-hidden rounded-lg bg-white p-4 shadow dark:bg-gray-800">
                    <div
                        class="relative"
                        x-data="{
                            dragging: false,
                            dragIndex: null,
                            handleClick(event) {
                                // Don't add space if we just finished dragging
                                if (this.dragging) {
                                    return;
                                }

                                const rect = event.currentTarget.getBoundingClientRect();
                                const x = ((event.clientX - rect.left) / rect.width) * 100;
                                const y = ((event.clientY - rect.top) / rect.height) * 100;

                                // Check if we clicked near an existing space
                                const clicked = event.target.closest('[data-space-index]');
                                if (clicked) {
                                    const index = parseInt(clicked.dataset.spaceIndex);
                                    $wire.selectSpace(index);
                                } else {
                                    $wire.addSpace(x, y);
                                }
                            },
                            handleDragStart(event, index) {
                                this.dragging = true;
                                this.dragIndex = index;
                                event.dataTransfer.effectAllowed = 'move';
                                event.dataTransfer.setData('text/html', event.target);
                            },
                            handleDragOver(event) {
                                if (event.preventDefault) {
                                    event.preventDefault();
                                }
                                event.dataTransfer.dropEffect = 'move';
                                return false;
                            },
                            handleDrop(event) {
                                if (event.stopPropagation) {
                                    event.stopPropagation();
                                }
                                if (this.dragIndex !== null) {
                                    const rect = event.currentTarget.getBoundingClientRect();
                                    const x = ((event.clientX - rect.left) / rect.width) * 100;
                                    const y = ((event.clientY - rect.top) / rect.height) * 100;

                                    $wire.moveSpace(this.dragIndex, x, y);
                                    $wire.selectSpace(this.dragIndex);
                                }
                                return false;
                            },
                            handleDragEnd(event) {
                                // Small delay to prevent click event from firing
                                setTimeout(() => {
                                    this.dragging = false;
                                    this.dragIndex = null;
                                }, 100);
                            }
                        }"
                        @click="handleClick"
                        @dragover="handleDragOver"
                        @drop="handleDrop"
                    >
                        <img
                            src="/{{ $imagePath }}"
                            alt="Game Board"
                            class="w-full cursor-crosshair"
                        >

                        <!-- Space Markers -->
                        @foreach($board['spaces'] ?? [] as $index => $space)
                            <div
                                data-space-index="{{ $index }}"
                                draggable="true"
                                class="absolute flex h-8 w-8 -translate-x-1/2 -translate-y-1/2 transform cursor-move items-center justify-center rounded-full border-2 text-xs font-bold transition-all
                                    {{ $selectedSpace === $index
                                        ? 'border-red-500 bg-red-500 text-white scale-125 z-10'
                                        : 'border-blue-500 bg-blue-500 text-white hover:scale-110'
                                    }}"
                                style="left: {{ $space['x'] }}%; top: {{ $space['y'] }}%;"
                                @click.stop="$wire.selectSpace({{ $index }})"
                                @dragstart="handleDragStart($event, {{ $index }})"
                                @dragend="handleDragEnd($event)"
                            >
                                {{ $index }}
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-4 text-sm text-gray-600 dark:text-gray-400">
                        Total Spaces: {{ count($board['spaces'] ?? []) }}
                    </div>
                </div>
            </div>

            <!-- Space Editor Panel -->
            <div class="lg:col-span-1">
                <div class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                    <div class="border-b border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-900">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            {{ $selectedSpace !== null ? 'Edit Space #' . $selectedSpace : 'No Space Selected' }}
                        </h2>
                    </div>

                    @if($selectedSpace !== null && isset($board['spaces'][$selectedSpace]))
                        <div class="space-y-4 p-4">
                            @php $space = $board['spaces'][$selectedSpace]; @endphp

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Index
                                </label>
                                <input
                                    type="number"
                                    value="{{ $space['index'] }}"
                                    disabled
                                    class="mt-1 w-full rounded-lg border border-gray-300 bg-gray-100 px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                >
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    X Position (%)
                                </label>
                                <input
                                    type="number"
                                    step="0.01"
                                    value="{{ $space['x'] }}"
                                    wire:change="updateSpace({{ $selectedSpace }}, 'x', $event.target.value)"
                                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                >
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Y Position (%)
                                </label>
                                <input
                                    type="number"
                                    step="0.01"
                                    value="{{ $space['y'] }}"
                                    wire:change="updateSpace({{ $selectedSpace }}, 'y', $event.target.value)"
                                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                >
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Stage
                                </label>
                                <select
                                    wire:change="updateSpace({{ $selectedSpace }}, 'stage', $event.target.value)"
                                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                >
                                    <option value="start" {{ $space['stage'] === 'start' ? 'selected' : '' }}>Start</option>
                                    <option value="early" {{ $space['stage'] === 'early' ? 'selected' : '' }}>Early</option>
                                    <option value="commons" {{ $space['stage'] === 'commons' ? 'selected' : '' }}>Commons</option>
                                    <option value="lords" {{ $space['stage'] === 'lords' ? 'selected' : '' }}>Lords</option>
                                    <option value="implementation" {{ $space['stage'] === 'implementation' ? 'selected' : '' }}>Implementation</option>
                                    <option value="end" {{ $space['stage'] === 'end' ? 'selected' : '' }}>End</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Deck
                                </label>
                                <select
                                    wire:change="updateSpace({{ $selectedSpace }}, 'deck', $event.target.value)"
                                    class="mt-1 w-full rounded-lg border border-gray-300 px-3 py-2 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                >
                                    <option value="none" {{ $space['deck'] === 'none' ? 'selected' : '' }}>None</option>
                                    <option value="early" {{ $space['deck'] === 'early' ? 'selected' : '' }}>Early</option>
                                    <option value="commons" {{ $space['deck'] === 'commons' ? 'selected' : '' }}>Commons</option>
                                    <option value="lords" {{ $space['deck'] === 'lords' ? 'selected' : '' }}>Lords</option>
                                    <option value="pingpong" {{ $space['deck'] === 'pingpong' ? 'selected' : '' }}>Ping Pong</option>
                                    <option value="implementation" {{ $space['deck'] === 'implementation' ? 'selected' : '' }}>Implementation</option>
                                </select>
                            </div>

                            <button
                                wire:click="deleteSpace({{ $selectedSpace }})"
                                class="w-full rounded-lg bg-red-600 px-4 py-2 text-white hover:bg-red-700"
                            >
                                Delete Space
                            </button>
                        </div>
                    @else
                        <div class="p-4 text-center text-gray-500 dark:text-gray-400">
                            <p>Click a space to edit it, drag to move it, or click the board to add a new space.</p>
                        </div>
                    @endif
                </div>

                <!-- Spaces List -->
                <div class="mt-6 overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                    <div class="border-b border-gray-200 bg-gray-50 px-4 py-3 dark:border-gray-700 dark:bg-gray-900">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">All Spaces</h2>
                    </div>
                    <div class="max-h-96 divide-y divide-gray-200 overflow-y-auto dark:divide-gray-700">
                        @forelse($board['spaces'] ?? [] as $index => $space)
                            <div
                                wire:click="selectSpace({{ $index }})"
                                class="cursor-pointer px-4 py-3 transition-colors hover:bg-gray-50 dark:hover:bg-gray-700
                                    {{ $selectedSpace === $index ? 'bg-blue-50 dark:bg-blue-900/20' : '' }}"
                            >
                                <div class="flex items-center justify-between">
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-white">
                                            Space {{ $index }}
                                        </div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ $space['stage'] }} - {{ $space['deck'] }}
                                        </div>
                                    </div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400">
                                        ({{ number_format($space['x'], 1) }}, {{ number_format($space['y'], 1) }})
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="px-4 py-8 text-center text-gray-500 dark:text-gray-400">
                                No spaces yet. Click on the board to add one!
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('livewire:init', function () {
        Livewire.on('board-saved', () => {
            alert('Board saved successfully!');
        });
    });
</script>
