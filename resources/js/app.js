import './bootstrap';
import game from './game';

// Register Alpine components
document.addEventListener('alpine:init', () => {
    Alpine.data('game', game);
});
