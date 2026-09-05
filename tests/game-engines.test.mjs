import { readFileSync } from 'node:fs';
import vm from 'node:vm';
import test from 'node:test';
import assert from 'node:assert/strict';

function engine(file, factory, data = []) {
    const source = readFileSync(new URL(`../resources/views/games/${file}-game.blade.php`, import.meta.url), 'utf8');
    const script = source.match(/<script>([\s\S]*?)<\/script>/)[1]
        .replace(/const scratchCardEndpoint = @json\(route\([\s\S]*?\)\);/, "const scratchCardEndpoint = '/scratch-cards/__NUMBER__';")
        .replace(/@json\([^)]*\)/g, JSON.stringify(data))
        .replace(/\{\{[^}]*\}\}/g, '1');
    const intervals = new Map();
    const frames = [];
    const saved = new Map();
    const scope = { console, setTimeout: f => f(), setInterval: f => { const id = Symbol(); intervals.set(id, f); return id; }, clearInterval: id => intervals.delete(id), requestAnimationFrame: f => frames.push(f), performance: { now: () => 0 }, alert: () => {}, document: { querySelectorAll: () => [] } };
    vm.createContext(scope);
    scope.localStorage = {getItem: key => saved.get(key) ?? null, setItem: (key, value) => saved.set(key, value)};
    scope.document.querySelector = () => null;
    vm.runInContext(script, scope);
    const game = vm.runInContext(`${factory}()`, scope);
    game.$nextTick = callback => callback();
    return { game, scope, intervals, frames };
}

test('who: all four answer combinations identify the person and score once', () => {
    for (const male of ['me', 'partner']) for (const female of ['me', 'partner']) {
        const { game } = engine('who', 'whoGame', [{id: 1}]);
        game.init(); game.screen = 'game'; game.chooseMale(male); game.chooseFemale(female);
        game.reveal(); game.reveal();
        assert.equal(game.isMatch, male !== female);
        assert.equal(game.score + game.challenges, 1);
        game.nextQuestion(); assert.equal(game.screen, 'result');
        game.resetGame(); assert.equal(game.score, 0); assert.equal(game.step, 1);
    }
});

test('know me: filters, alternating players, answers, final result and reset', () => {
    const { game } = engine('know-me', 'knowMeGame', [{id: 1, category: 'daily'}, {id: 2, category: 'future'}]);
    game.toggleCat('daily'); assert.equal(game.filteredQuestions.length, 1);
    game.toggleCat(null); game.startingPlayer = 'female'; game.startGame();
    assert.equal(game.currentQ.about, 'female');
    game.subjectAnswer = ' A '; game.guessAnswer = 'a'; game.step = 2;
    game.showReveal(); game.showReveal(); assert.equal(game.score.correct, 1);
    game.nextQuestion(); assert.equal(game.currentQ.about, 'male');
    game.step = 2; game.showReveal(); assert.equal(game.step, 2);
    game.subjectAnswer = 'A'; game.guessAnswer = 'B'; game.showReveal(); assert.equal(game.score.wrong, 1);
    game.nextQuestion(); assert.equal(game.screen, 'result');
    game.resetGame(); assert.equal(game.totalQ, 2); assert.equal(game.score.correct, 0);
});

test('challenge: timer cannot double start, totals stay fixed, finish and restart clear timers', () => {
    const view = readFileSync(new URL('../resources/views/games/challenge-game.blade.php', import.meta.url), 'utf8');
    assert.doesNotMatch(view.match(/<button @click="nextCard\(\)"[\s\S]*?<\/button>/)[0], /:disabled="deck.length === 0"/);
    const { game, intervals } = engine('challenge', 'challengeGame', [{id: 1, timer: 2}, {id: 2, timer: 2}]);
    game.init(); game.startGame(); assert.equal(game.totalCards, 2);
    game.flipCard(); game.startTimer(); game.startTimer(); assert.equal(intervals.size, 1);
    for (const tick of intervals.values()) { tick(); tick(); }
    assert.equal(game.timerLeft, 0); assert.equal(game.timerDone, true);
    game.nextCard(); game.nextCard(); assert.equal(game.done, 1); assert.equal(game.totalCards, 2);
    game.flipCard(); game.startTimer(); game.skipCard(); assert.equal(intervals.size, 0); assert.equal(game.screen, 'done');
    game.startGame(); game.flipCard(); game.startTimer(); game.resetGame(); assert.equal(intervals.size, 0);
});

test('card: load, target player, exhaustion, reset and failed fetch', async () => {
    const { game, scope } = engine('card', 'cardGame');
    scope.fetch = async () => ({ok: true, json: async () => [{id: 1, target: 'female'}, {id: 2, target: 'male'}]});
    game.shuffle = items => items;
    await game.selectLevel('easy', 'Easy', '#ffffff');
    assert.equal(game.currentPlayer, 'female'); assert.equal(game.totalCards, 2);
    game.flipCard(); game.nextCard(); game.nextCard();
    assert.equal(game.cardIndex, 1); assert.equal(game.currentPlayer, 'male');
    game.flipCard(); game.nextCard(); assert.equal(game.cardIndex, 1);
    game.resetGame(); assert.equal(game.currentCard, null);
    scope.fetch = async () => ({ok: false});
    await game.selectLevel('easy', 'Easy', '#ffffff'); assert.equal(game.loading, false); assert.equal(game.currentCard, null);
});

test('spinner: repeated spins move forward and pointer matches the selected sector', () => {
    const { game, frames } = engine('spinner', 'spinnerGame', [{id: 1}, {id: 2}, {id: 3}]);
    game.drawWheel = () => {}; game.playSound = () => {};
    for (let i = 0; i < 30; i++) {
        const previous = game.currentAngle;
        game.spin(); game.spin(); assert.equal(frames.length, 1);
        frames.shift()(5000);
        assert.ok(game.currentAngle - previous >= 5 * 2 * Math.PI - 1e-9);
        const arc = 2 * Math.PI / game.items.length;
        const pointer = ((-game.currentAngle % (2 * Math.PI)) + 2 * Math.PI) % (2 * Math.PI);
        assert.equal(game.items[Math.floor(pointer / arc)].id, game.result.id);
    }
    assert.equal(game.history.length, 30);
});

test('scratch: reveal is not completion, points are awarded once, skip and reset work', () => {
    const { game, scope } = engine('scratch', 'scratchGame', [{number: 1, level: 3}, {number: 2, level: 2}]);
    game.init();
    const child = vm.runInContext('scratchCard(0)', scope);
    child.canvas = {width: 100, height: 100, style: {}};
    child.ctx = {clearRect() {}}; child.drawOverlay = () => {};
    scope.document.querySelectorAll = () => [{closest() {return {};}}];
    scope.Alpine = {$data: () => child};
    child.autoReveal(); assert.equal(child.revealed, true); assert.equal(game.revealedCount, 1); assert.equal(game.score, 0);
    game.completeTask(); assert.equal(game.score, 3);
    game.openTask(1); game.completeTask(); assert.equal(game.score, 3);
    game.markRevealed(2); game.openTask(2); game.skipTask(); assert.equal(game.score, 3); assert.equal(game.skippedNumbers.length, 1);
    game.openTask(2); game.completeTask(); assert.equal(game.score, 5); assert.equal(game.skippedNumbers.length, 0);
    game.toggleBonus('phones'); assert.equal(game.score, 8);
    game.toggleBonus('phones'); assert.equal(game.score, 5);
    game.resetGame(); assert.equal(child.revealed, false); assert.equal(game.revealedCount, 0); assert.equal(game.score, 0);
});

test('scratch: restore saved progress, reject stale IDs and survive unavailable storage', () => {
    const {game,scope} = engine('scratch','scratchGame',[{number:1,level:2}]);
    game.init(); game.markRevealed(1); game.openTask(1); game.completeTask();
    const restored = vm.runInContext('scratchGame()',scope); restored.init(); assert.equal(restored.score,2);
    scope.localStorage.setItem(game.storageKey,JSON.stringify({revealedNumbers:[1,1,999],completedNumbers:[1,1,999],bonusIds:['phones','phones','unknown']}));
    restored.init(); assert.equal(restored.revealedCount,1); assert.equal(restored.score,5);
    scope.localStorage.setItem = () => {throw new Error('Storage disabled');};
    restored.save(); assert.equal(restored.saveError,true);
});

test('scratch: transparent canvas corners do not trigger a reveal, only the silver circle counts', () => {
    const {game,scope} = engine('scratch','scratchGame',[{number:1,level:1}]); game.init();
    const child = vm.runInContext('scratchCard(0)',scope);
    child.canvas={width:160,height:160};
    const pixels = new Uint8ClampedArray(160*160*4);
    for(let y=0;y<160;y++)for(let x=0;x<160;x++)if(Math.hypot(x-80,y-80)<79)pixels[(y*160+x)*4+3]=255;
    child.ctx={getImageData:()=>({data:pixels}),clearRect(){}};
    child.checkReveal(); assert.equal(child.revealed,false);
    pixels.fill(0); child.checkReveal(); assert.equal(child.revealed,true); assert.equal(game.score,0);
});

test('scratch: opening a task refreshes stale image data', async () => {
    const {game,scope} = engine('scratch','scratchGame',[{number:10,level:1,image:null}]);
    scope.fetch = async () => ({ok:true,json:async () => ({number:10,level:1,content:'Updated task',image:'/storage/scratch/new.jpg?v=2'})});
    game.init(); game.markRevealed(10); await game.openTask(10);
    assert.equal(game.activeCard.image,'/storage/scratch/new.jpg?v=2');
    assert.equal(game.activeCard.content,'Updated task');
});

test('romantic content can be skipped without adding a score or challenge', async () => {
    const who = engine('who', 'whoGame', [{id: 1}, {id: 2}]).game;
    who.init(); who.screen = 'game'; who.nextQuestion();
    assert.equal(who.score, 0); assert.equal(who.challenges, 0); assert.equal(who.currentIndex, 1);
    const know = engine('know-me', 'knowMeGame', [{id: 1}, {id: 2}]).game;
    know.startGame(); know.nextQuestion();
    assert.equal(know.score.correct, 0); assert.equal(know.score.wrong, 0); assert.equal(know.currentIndex, 1);
    const {game: cards, scope} = engine('card', 'cardGame');
    scope.fetch = async () => ({ok: true, json: async () => [{id: 1}, {id: 2}]});
    await cards.selectLevel('easy', 'Easy', '#ffffff');
    cards.skipCard(); assert.equal(cards.cardIndex, 1); assert.equal(cards.isFlipped, false);
    cards.skipCard(); assert.equal(cards.screen, 'levels'); assert.equal(cards.currentCard, null);
});
