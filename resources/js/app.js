import './bootstrap';
import singlePlayerGame from './single-player-game';

// Register Alpine components
document.addEventListener('alpine:init', () => {
    Alpine.data('singlePlayerGame', singlePlayerGame);
});
