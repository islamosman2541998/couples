export function cellPoint(number) {
    const row = Math.floor((number - 1) / 10);
    const offset = (number - 1) % 10;
    return {x: (row % 2 === 0 ? 9 - offset : offset) * 10 + 5, y: (9 - row) * 10 + 5};
}

export function createSnakesGame(data = {}, environment = {}) {
    const random = environment.random ?? Math.random;
    const storage = () => environment.storage ?? globalThis.localStorage;
    const schedule = environment.setTimeout ?? globalThis.setTimeout;
    const cancel = environment.clearTimeout ?? globalThis.clearTimeout;
    const timers = new Map();
    let generation = 0;
    const cells = data.cells ?? [];
    const links = data.links ?? [];
    const fingerprint = JSON.stringify([cells, links]);
    const defaults = () => [{name: 'الزوج', position: 0, completed: 0, skipped: 0}, {name: 'الزوجة', position: 0, completed: 0, skipped: 0}];

    return {
        screen: 'intro', phase: 'ready', players: defaults(), startingPlayer: 0, currentPlayer: 0,
        prize: 'الفائز يختار موعدنا الجاي', dice: 1, displayDice: 1, turns: 0,
        displayPositions: [0, 0], pending: null, notice: '', history: [], savedRound: null,
        storageAvailable: true, peekNumber: null, showPaths: true, reducedMotion: false,
        cells, links,
        get boardNumbers() { return Array.from({length: 100}, (_, index) => { const row = 9 - Math.floor(index / 10), offset = index % 10; return row * 10 + (row % 2 === 0 ? 10 - offset : 1 + offset); }); },
        get activePlayer() { return this.players[this.currentPlayer]; },
        get busy() { return this.phase === 'rolling'; },
        get winner() { return this.players.find(player => player.position === 100) ?? null; },
        get task() { return this.pending ? this.cell(this.pending.destination) : null; },
        get peekCell() { return this.peekNumber ? this.cell(this.peekNumber) : null; },
        get canPlay() { return this.cells.length === 100; },
        get dicePips() {
            return {1: [5], 2: [1, 9], 3: [1, 5, 9], 4: [1, 3, 7, 9], 5: [1, 3, 5, 7, 9], 6: [1, 3, 4, 6, 7, 9]}[this.displayDice] ?? [5];
        },
        init() {
            this.reducedMotion = globalThis.matchMedia?.('(prefers-reduced-motion: reduce)').matches ?? false;
            this.loadSaved();
        },
        cell(number) { return this.cells.find(cell => cell.number === number) ?? {number, title: 'استراحة', content: 'خدوا لحظة هادية مع بعض، وبعدها كمّلوا اللعب.', mood: 'warm', active: false}; },
        point: cellPoint,
        icon(number) {
            if (number === 100) return '♛';
            const link = this.links.find(link => link.from === number);
            if (link) return link.to > number ? '↗' : '↘';
            if (!this.cell(number).active) return '☾';
            return {warm: '✦', playful: '✧', romantic: '♥'}[this.cell(number).mood] ?? '✦';
        },
        cellLabel(number) {
            const link = this.links.find(link => link.from === number);
            return `خانة ${number}: ${this.cell(number).title}` + (link ? `، ${link.to > number ? 'سلم' : 'تعبان'} إلى ${link.to}` : '');
        },
        cellClass(number) {
            const link = this.links.find(link => link.from === number);
            return { 'sl-finish': number === 100, 'sl-ladder-cell': link?.to > number, 'sl-snake-cell': link?.to < number };
        },
        tokenStyle(index) {
            const point = this.point(this.displayPositions[index] || 1);
            return {left: `${point.x + (index === 0 ? -1.5 : 1.5)}%`, top: `${point.y + 1.4}%`};
        },
        ladder(link) {
            const start = this.point(link.from), end = this.point(link.to);
            const dx = end.x - start.x, dy = end.y - start.y, length = Math.hypot(dx, dy);
            const nx = -dy / length * 1.2, ny = dx / length * 1.2;
            const line = (a, b) => `M ${a.x} ${a.y} L ${b.x} ${b.y}`;
            let path = line({x: start.x + nx, y: start.y + ny}, {x: end.x + nx, y: end.y + ny}) + ' ' + line({x: start.x - nx, y: start.y - ny}, {x: end.x - nx, y: end.y - ny});
            for (let step = 0.15; step < 1; step += 0.15) {
                const x = start.x + dx * step, y = start.y + dy * step;
                path += ' ' + line({x: x + nx, y: y + ny}, {x: x - nx, y: y - ny});
            }
            return path;
        },
        snake(link) {
            const start = this.point(link.from), end = this.point(link.to);
            return `M ${start.x} ${start.y} C ${start.x + 7} ${start.y + 6}, ${end.x - 7} ${end.y - 6}, ${end.x} ${end.y}`;
        },
        startGame() {
            if (!this.canPlay) return;
            this.stopAnimation();
            this.players = this.players.map((player, index) => ({...defaults()[index], name: player.name.trim().slice(0, 24) || defaults()[index].name}));
            this.prize = this.prize.trim().slice(0, 160) || 'الفائز يختار موعدنا الجاي';
            this.currentPlayer = this.startingPlayer === 1 ? 1 : 0;
            this.turns = 0; this.dice = 1; this.displayDice = 1; this.displayPositions = [0, 0];
            this.pending = null; this.history = []; this.notice = 'السباق يبدأ من خارج اللوحة. ارمِ النرد وخلي الحظ يختار!';
            this.phase = 'ready'; this.screen = 'game'; this.savedRound = null;
            this.save();
        },
        async roll() {
            if (this.screen !== 'game' || this.phase !== 'ready' || this.winner) return;
            const token = generation;
            const playerIndex = this.currentPlayer;
            const from = this.activePlayer.position;
            this.dice = Math.min(6, Math.max(1, Math.floor(random() * 6) + 1));
            const overshoot = from + this.dice > 100;
            const landed = overshoot ? from : from + this.dice;
            const link = overshoot ? null : this.links.find(link => link.from === landed);
            const destination = link?.to ?? landed;
            this.pending = {player: playerIndex, from, landed, destination, overshoot, die: this.dice};
            this.players[playerIndex].position = destination;
            this.turns++;
            this.phase = 'rolling';
            this.notice = 'النرد بيتحرك…';
            // Persist the committed result before animation so reloading cannot reroll it.
            this.save();
            if (!this.reducedMotion) {
                for (let frame = 0; frame < 6; frame++) {
                    this.displayDice = (this.displayDice % 6) + 1;
                    await this.wait(75);
                    if (generation !== token) return;
                }
            }
            this.displayDice = this.dice;
            if (!overshoot) {
                for (let position = from + 1; position <= landed; position++) {
                    this.displayPositions[playerIndex] = position;
                    if (!this.reducedMotion) await this.wait(110);
                    if (generation !== token) return;
                }
                if (link && !this.reducedMotion) await this.wait(350);
                if (generation !== token) return;
                this.displayPositions[playerIndex] = destination;
            }
            this.resolveLanding();
        },
        landingNotice(pending) {
            if (pending.overshoot) return `محتاج ${100 - pending.from} بالظبط للوصول. الدور اتنقل لشريكك.`;
            if (pending.destination > pending.landed) return `سلم الحظ! من ${pending.landed} إلى ${pending.destination} ↗`;
            if (pending.destination < pending.landed) return `مفاجأة التعبان! من ${pending.landed} إلى ${pending.destination} ↘`;
            return `النرد ${pending.die} · وصلت للخانة ${pending.destination}`;
        },
        resolveLanding() {
            if (!this.pending) return;
            this.notice = this.landingNotice(this.pending);
            if (this.pending.overshoot) {
                this.addHistory('رقم أكبر من المطلوب');
                this.pending = null; this.currentPlayer = 1 - this.currentPlayer; this.phase = 'ready';
            } else if (this.pending.destination === 100) {
                this.addHistory('وصل للقمة 👑'); this.phase = 'won';
            } else {
                this.phase = 'challenge';
            }
            this.save();
        },
        completeTask(completed) {
            if (this.phase !== 'challenge' || !this.pending || typeof completed !== 'boolean') return;
            if (this.task.active) this.activePlayer[completed ? 'completed' : 'skipped']++;
            this.addHistory(this.task.active ? (completed ? 'نفّذ التحدي' : 'تخطّى التحدي') : 'استراحة');
            this.currentPlayer = 1 - this.currentPlayer;
            this.pending = null; this.phase = 'ready';
            this.notice = `الدور على ${this.activePlayer.name}… ارمِ النرد!`;
            this.save();
        },
        addHistory(action) {
            this.history.unshift({name: this.activePlayer.name, die: this.dice, position: this.activePlayer.position, action});
            this.history = this.history.slice(0, 6);
        },
        wait(ms) { return new Promise(resolve => { const id = schedule(() => { timers.delete(id); resolve(); }, ms); timers.set(id, resolve); }); },
        stopAnimation() { generation++; for (const [id, resolve] of timers) { cancel(id); resolve(); } timers.clear(); },
        destroy() { this.stopAnimation(); },
        newGame() {
            this.stopAnimation(); this.screen = 'intro'; this.phase = 'ready'; this.pending = null;
            this.players = this.players.map((player, index) => ({...defaults()[index], name: player.name}));
            this.displayPositions = [0, 0]; this.history = []; this.turns = 0; this.savedRound = null;
            try { storage().removeItem(data.storageKey); } catch { this.storageAvailable = false; }
        },
        save() {
            let phase = this.phase, currentPlayer = this.currentPlayer, pending = this.pending;
            let notice = this.notice;
            if (phase === 'rolling') {
                notice = this.landingNotice(pending);
                phase = pending.overshoot ? 'ready' : pending.destination === 100 ? 'won' : 'challenge';
                if (pending.overshoot) { currentPlayer = 1 - currentPlayer; pending = null; }
            }
            const saved = {version: 1, fingerprint, players: this.players, currentPlayer, startingPlayer: this.startingPlayer, prize: this.prize, dice: this.dice, turns: this.turns, phase, pending, notice, history: this.history};
            try { storage().setItem(data.storageKey, JSON.stringify(saved)); } catch { this.storageAvailable = false; }
        },
        loadSaved() {
            try {
                const saved = JSON.parse(storage().getItem(data.storageKey));
                if (!saved || saved.version !== 1 || saved.fingerprint !== fingerprint) return;
                const integer = (value, min, max) => Number.isInteger(value) && value >= min && value <= max;
                if (!Array.isArray(saved.players) || saved.players.length !== 2 || !saved.players.every(p => typeof p.name === 'string' && p.name.length > 0 && p.name.length <= 24 && integer(p.position, 0, 100) && integer(p.completed, 0, 100000) && integer(p.skipped, 0, 100000))) return;
                if (!integer(saved.currentPlayer, 0, 1) || !integer(saved.startingPlayer, 0, 1) || !integer(saved.dice, 1, 6) || !integer(saved.turns, 0, 100000)) return;
                if (!['ready', 'challenge', 'won'].includes(saved.phase) || typeof saved.prize !== 'string' || saved.prize.length > 160) return;
                if (saved.phase === 'won' && saved.players[saved.currentPlayer].position !== 100) return;
                if (saved.phase !== 'won' && saved.players.some(p => p.position === 100)) return;
                if (saved.phase === 'challenge') {
                    const p = saved.pending;
                    if (!p || p.player !== saved.currentPlayer || !integer(p.from, 0, 99) || !integer(p.landed, 1, 99) || !integer(p.destination, 1, 99) || p.overshoot !== false || p.die !== saved.dice || p.landed !== p.from + p.die || p.destination !== (links.find(link => link.from === p.landed)?.to ?? p.landed) || saved.players[p.player].position !== p.destination) return;
                }
                this.savedRound = saved;
            } catch { this.storageAvailable = false; }
        },
        resumeGame() {
            if (!this.savedRound) return;
            const saved = this.savedRound;
            this.players = saved.players.map(p => ({...p}));
            for (const key of ['currentPlayer', 'startingPlayer', 'prize', 'dice', 'turns', 'phase']) this[key] = saved[key];
            this.pending = saved.phase === 'challenge' ? saved.pending : null;
            this.notice = typeof saved.notice === 'string' ? saved.notice.slice(0, 250) : '';
            this.history = Array.isArray(saved.history) ? saved.history.filter(h => h && typeof h.name === 'string' && typeof h.action === 'string' && Number.isInteger(h.die) && Number.isInteger(h.position)).slice(0, 6) : [];
            this.displayPositions = this.players.map(p => p.position); this.displayDice = this.dice;
            this.screen = 'game'; this.savedRound = null;
        },
        inspect(number) {
            this.peekNumber = number;
            this.$refs.peek.showModal();
        },
    };
}
