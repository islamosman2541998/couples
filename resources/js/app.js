import './bootstrap';

import Alpine from 'alpinejs';
import { createSnakesGame } from './games/snakes-game';
import { checkout } from './checkout';

window.Alpine = Alpine;
Alpine.data('snakesGame', () => createSnakesGame(window.snakesGameData));
Alpine.data('checkout', checkout);

Alpine.start();

import './home';

import './analytics';
