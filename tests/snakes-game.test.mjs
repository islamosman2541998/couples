import test from 'node:test';
import assert from 'node:assert/strict';
import {createSnakesGame, cellPoint} from '../resources/js/games/snakes-game.js';

const cells = Array.from({length: 100}, (_, i) => ({number: i + 1, title: `Task ${i + 1}`, content: `Content ${i + 1}`, mood: 'romantic', active: true}));
const links = [{from: 3, to: 22}, {from: 27, to: 6}];
function setup(die = 3, saved = new Map()) {
    const storage = {getItem: key => saved.get(key) ?? null, setItem: (key, value) => saved.set(key, value), removeItem: key => saved.delete(key)};
    const game = createSnakesGame({cells, links, storageKey: 'test'}, {random: () => (die - 1) / 6, storage});
    game.reducedMotion = true;
    return {game, storage, saved};
}

test('the entire serpentine board matches SVG coordinates and never skips a square', () => {
    const {game} = setup();
    assert.equal(new Set(game.boardNumbers).size, 100);
    game.boardNumbers.forEach((number, i) => assert.deepEqual(cellPoint(number), {x: i % 10 * 10 + 5, y: Math.floor(i / 10) * 10 + 5}));
    assert.deepEqual(cellPoint(1), {x: 95, y: 95});
    assert.deepEqual(cellPoint(100), {x: 95, y: 5});
});

test('ladder, destination task, completion, repeat click protection and alternating turns', async () => {
    const {game} = setup(); game.startGame();
    await game.roll();
    assert.equal(game.players[0].position, 22); assert.equal(game.task.number, 22);
    assert.equal(game.phase, 'challenge');
    await game.roll(); assert.equal(game.turns, 1);
    game.completeTask(true); game.completeTask(true);
    assert.equal(game.players[0].completed, 1); assert.equal(game.currentPlayer, 1);
    await game.roll(); game.completeTask(false);
    assert.equal(game.players[1].position, 22); assert.equal(game.players[1].skipped, 1);
    assert.equal(game.currentPlayer, 0);
});

test('snake takes player down and presents the final square challenge', async () => {
    const {game} = setup(2); game.startGame(); game.players[0].position = 25;
    await game.roll(); assert.equal(game.players[0].position, 6);
    assert.equal(game.task.number, 6); assert.match(game.notice, /التعبان/);
});

test('six gives no extra turn, exact finish wins, overshooting keeps position', async () => {
    const {game} = setup(6); game.startGame(); await game.roll(); game.completeTask(false);
    assert.equal(game.currentPlayer, 1);
    game.players[1].position = 97; await game.roll();
    assert.equal(game.players[1].position, 97); assert.equal(game.currentPlayer, 0); assert.equal(game.phase, 'ready');
    game.players[0].position = 94; await game.roll();
    assert.equal(game.winner.name, 'الزوج'); assert.equal(game.phase, 'won');
    const turns = game.turns; await game.roll(); assert.equal(game.turns, turns);
});

test('restore waiting challenge, names, prize and completed counts; reset clears saved state', async () => {
    const first = setup(1); first.game.players[0].name = '  A  '; first.game.startingPlayer = 1;
    first.game.prize = 'Our date'; first.game.startGame(); await first.game.roll();
    const restored = setup(1, first.saved); restored.game.loadSaved(); assert.ok(restored.game.savedRound);
    restored.game.resumeGame(); assert.equal(restored.game.currentPlayer, 1); assert.equal(restored.game.task.number, 1);
    assert.equal(restored.game.players[0].name, 'A'); assert.equal(restored.game.prize, 'Our date');
    restored.game.completeTask(true); assert.equal(restored.game.players[1].completed, 1);
    restored.game.newGame(); assert.equal(first.saved.size, 0); assert.equal(restored.game.screen, 'intro');
});

test('invalid saves and changed boards are ignored, storage failures do not block playing', async () => {
    const {game, storage, saved} = setup(); game.startGame();
    const baseline = JSON.parse(storage.getItem('test'));
    for (const mutate of [s => s.players[0].position = 200, s => s.currentPlayer = 9, s => s.phase = 'won', s => s.fingerprint = 'old', s => s.phase = 'challenge']) {
        const state = structuredClone(baseline); mutate(state); storage.setItem('test', JSON.stringify(state));
        const next = setup(1, saved).game; next.loadSaved(); assert.equal(next.savedRound, null);
    }
    const blocked = createSnakesGame({cells, links, storageKey: 'x'}, {storage: {getItem() {throw Error();}, setItem() {throw Error();}, removeItem() {throw Error();}}});
    blocked.init(); blocked.reducedMotion = true; blocked.startGame(); await blocked.roll();
    assert.equal(blocked.storageAvailable, false); assert.equal(blocked.phase, 'challenge');
});

test('committed dice result survives refresh during animation and repeated roll is ignored', async () => {
    const {game, saved} = setup(); game.startGame(); game.reducedMotion = false;
    const rolling = game.roll(); await game.roll(); assert.equal(game.turns, 1);
    const restored = setup(1, saved).game; restored.loadSaved(); restored.resumeGame();
    assert.equal(restored.phase, 'challenge'); assert.equal(restored.players[0].position, 22); assert.equal(restored.dice, 3);
    game.destroy(); await rolling; assert.equal(game.phase, 'rolling');
});

test('disabled task is a rest and does not affect completed or skipped totals', async () => {
    const restCells = cells.map(cell => ({...cell, active: false}));
    const game = createSnakesGame({cells: restCells, links: [], storageKey: 'rest'}, {random: () => 0, storage: {setItem() {}}});
    game.reducedMotion = true; game.startGame(); await game.roll(); game.completeTask(true);
    assert.equal(game.players[0].completed, 0); assert.equal(game.players[0].skipped, 0); assert.equal(game.currentPlayer, 1);
});
