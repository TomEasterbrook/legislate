<?php

use Livewire\Volt\Volt;

it('can render', function () {
    $component = Volt::test('single-player-game');

    $component->assertSee('');
});
