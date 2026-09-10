import './bootstrap';

import Alpine from 'alpinejs';
import { createSnakesGame } from './games/snakes-game';

window.Alpine = Alpine;
Alpine.data('snakesGame', () => createSnakesGame(window.snakesGameData));

Alpine.start();

import './home';
