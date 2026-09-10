import { readFileSync } from 'node:fs';
import vm from 'node:vm';
import test from 'node:test';
import assert from 'node:assert/strict';

function setup(count = 50) {
    const view = readFileSync(new URL('../resources/views/games/control-game.blade.php', import.meta.url), 'utf8');
    const cards = Array.from({length: count}, (_, i) => ({id: i + 1, title: `Card ${i + 1}`}));
    const source = view.match(/<script>([\s\S]*?)<\/script>/)[1].replace('@json($cards)', JSON.stringify(cards));
    const timers = new Map();
    const scope = {setTimeout: f => { const key = Symbol(); timers.set(key, f); return key; }, clearTimeout: key => timers.delete(key)};
    vm.createContext(scope);
    vm.runInContext(source, scope);
    return {game: vm.runInContext('controlGame()', scope), timers, tick() {for (const [key, fn] of timers) { timers.delete(key); fn(); }} };
}

test('50 unique cards, alternating turns, independent first free refusals and negative scores', () => {
    const {game, tick} = setup();
    game.startingPlayer = 1;
    game.startGame();
    const ids = new Set();
    for (let i = 0; i < 50; i++) {
        assert.equal(game.currentPlayer, i % 2 === 0 ? 1 : 0);
        ids.add(game.currentCard.id);
        game.answer(false);
        if (i < 2) assert.equal(game.players[i === 0 ? 1 : 0].score, 0);
        tick();
    }
    assert.equal(ids.size, 50);
    assert.equal(game.screen, 'done');
    assert.equal(game.answeredCount, 50);
    for (const player of game.players) { assert.equal(player.score, -48); assert.equal(player.refused, 25); }
    assert.match(game.winnerText, /تعادل/);
});

test('rapid repeated answers score only once; pause and resume preserve the current card', () => {
    const {game, tick} = setup(4);
    game.startGame();
    game.answer(true); game.answer(true); game.answer(false);
    assert.equal(game.currentIndex, 1);
    assert.equal(game.players[0].score, 2);
    const card = game.currentCard.id;
    game.screen = 'intro'; tick(); game.answer(false);
    assert.equal(game.currentCard.id, card);
    assert.equal(game.answeredCount, 1);
    game.screen = 'game'; game.answer(false); tick();
    game.answer(false); tick(); game.answer(true);
    assert.equal(game.screen, 'done');
    assert.equal(game.players[0].score, 2);
    assert.equal(game.players[1].score, 2);
    game.answer(true);
    assert.equal(game.answeredCount, 4);
});

test('early finish, reset, starting player, empty deck and timer disposal', () => {
    const {game, timers} = setup(3);
    game.startingPlayer = 1; game.startGame(); game.answer(true);
    assert.equal(timers.size, 1);
    game.finishGame(); assert.equal(timers.size, 0);
    assert.match(game.winnerText, /الزوجة/);
    assert.equal(game.answeredCount, 1);
    game.resetGame(); assert.equal(game.screen, 'intro'); assert.equal(game.answeredCount, 0);
    assert.equal(game.hasRound, false);
    game.startGame(); assert.equal(game.currentPlayer, 1);
    game.answer(false); game.destroy(); assert.equal(timers.size, 0);
    const empty = setup(0).game;
    empty.startGame(); assert.equal(empty.screen, 'intro'); empty.answer(true); assert.equal(empty.answeredCount, 0);
});
