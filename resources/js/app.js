import './bootstrap';
import game from './game';
import multiplayerGame from './multiplayer';

// Register Alpine components
document.addEventListener('alpine:init', () => {
    Alpine.data('game', game);
    Alpine.data('multiplayerGame', multiplayerGame);
});
