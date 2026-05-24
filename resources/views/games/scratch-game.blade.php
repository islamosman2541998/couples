<x-app-layout>
    <x-slot name="title">{{ $game->name }}</x-slot>

    <div class="min-h-screen" x-data="scratchGame()" x-init="init()">

        <!-- Header -->
        <div class="max-w-4xl mx-auto px-4 pt-8 pb-4">
            <div class="text-center mb-8">
                <div class="text-5xl mb-3">🎰</div>
                <h1 class="text-2xl font-black mb-1">{{ $game->name }}</h1>
                <p class="text-gray-400 text-sm">اشخبط على كل كارت لتكشف ما بداخله</p>
            </div>

            <!-- Progress -->
            <div class="flex items-center justify-center gap-4 mb-2">
                <span class="text-sm text-gray-400">تم الكشف: <span class="text-purple-400 font-bold" x-text="revealedCount"></span> / {{ count($cards) }}</span>
                <div class="flex-1 max-w-xs bg-gray-800 rounded-full h-2">
                    <div class="bg-gradient-to-l from-pink-500 to-purple-500 h-2 rounded-full transition-all duration-500"
                         :style="`width: ${(revealedCount / {{ count($cards) }}) * 100}%`"></div>
                </div>
            </div>
        </div>

        <!-- Cards Grid -->
        <div class="max-w-4xl mx-auto px-4 pb-6">
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                @foreach($cards as $i => $card)
                    <div class="relative" x-data="scratchCard({{ $i }})">

                        <!-- Card Content (Under scratch) -->
                        <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden aspect-square flex flex-col items-center justify-center p-4 select-none">
                            @if($card['image'])
                                <img src="{{ $card['image'] }}" class="w-full h-3/4 object-cover rounded-xl mb-2">
                            @else
                                <div class="w-full h-3/4 bg-gradient-to-br from-purple-900/50 to-pink-900/50 rounded-xl flex items-center justify-center mb-2">
                                    <span class="text-5xl font-black text-white/20">{{ $card['number'] }}</span>
                                </div>
                            @endif
                            <div class="text-center">
                                <span class="text-lg font-black text-purple-400">#{{ $card['number'] }}</span>
                            </div>
                        </div>

                        <!-- Scratch Canvas Overlay -->
                        <canvas
                            class="absolute inset-0 w-full h-full rounded-2xl touch-none"
                            :class="revealed ? 'pointer-events-none' : 'cursor-crosshair'"
                            :id="`canvas-{{ $i }}`"
                            @mousedown="startScratch($event)"
                            @mousemove="doScratch($event)"
                            @mouseup="stopScratch()"
                            @mouseleave="stopScratch()"
                            @touchstart.prevent="startScratch($event)"
                            @touchmove.prevent="doScratch($event)"
                            @touchend="stopScratch()"
                        ></canvas>

                        <!-- Revealed Badge -->
                        <div x-show="revealed" x-cloak
                             class="absolute top-2 right-2 bg-green-500 text-white text-xs font-bold px-2 py-1 rounded-full">
                            ✓ مكشوف
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Reveal All Button -->
            <div class="text-center mt-6" x-show="revealedCount < {{ count($cards) }}">
                <button @click="revealAll()"
                        class="text-sm text-gray-500 hover:text-gray-300 underline transition-colors">
                    كشف الكل دفعة واحدة
                </button>
            </div>
        </div>

        <!-- Challenges List (shows when all revealed or at least one) -->
        <div x-show="revealedCount > 0" x-cloak
             class="max-w-4xl mx-auto px-4 pb-12">

            <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden">
                <div class="p-5 border-b border-gray-800 flex items-center gap-3">
                    <div class="text-2xl">📋</div>
                    <div>
                        <h2 class="font-bold text-white">الأحكام والتحديات</h2>
                        <p class="text-xs text-gray-400">الأرقام المكشوفة مع أحكامها</p>
                    </div>
                </div>

                <div class="divide-y divide-gray-800">
                    @foreach($cards as $card)
                        <div class="flex items-start gap-4 p-4"
                             :class="revealedNumbers.includes({{ $card['number'] }}) ? 'opacity-100' : 'opacity-30'">
                            <div class="flex-shrink-0 w-9 h-9 rounded-full bg-purple-900/60 border border-purple-700/50 flex items-center justify-center">
                                <span class="text-purple-300 font-black text-sm">{{ $card['number'] }}</span>
                            </div>
                            <p class="text-gray-200 text-sm leading-relaxed pt-1">{{ $card['content'] }}</p>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- All Done Message -->
            <div x-show="revealedCount >= {{ count($cards) }}" x-cloak
                 class="text-center mt-8">
                <div class="text-5xl mb-3">🎉</div>
                <h3 class="text-xl font-bold mb-2">أحسنتم!</h3>
                <p class="text-gray-400 text-sm mb-6">تم الكشف عن جميع الكروت</p>
                <button @click="resetGame()"
                        class="bg-gradient-to-l from-pink-600 to-purple-600 hover:from-pink-500 hover:to-purple-500 text-white px-8 py-3 rounded-2xl font-bold transition-all">
                    العب مرة أخرى 🔄
                </button>
            </div>
        </div>

    </div>

    @push('styles')
    <style>
        [x-cloak] { display: none !important; }
        canvas { display: block; }
    </style>
    @endpush

    @push('scripts')
    <script>
        const gameCards = @json($cards);

        // Global state for revealed tracking
        let globalRevealedCount = 0;
        let globalRevealedNumbers = [];
        let globalAlpineRoot = null;

        function scratchGame() {
            return {
                revealedCount: 0,
                revealedNumbers: [],

                init() {
                    globalAlpineRoot = this;
                },

                markRevealed(number) {
                    if (!this.revealedNumbers.includes(number)) {
                        this.revealedNumbers.push(number);
                        this.revealedCount = this.revealedNumbers.length;
                    }
                },

                revealAll() {
                    document.querySelectorAll('[id^="canvas-"]').forEach(canvas => {
                        const ctx = canvas.getContext('2d');
                        ctx.clearRect(0, 0, canvas.width, canvas.height);
                    });
                    gameCards.forEach(card => this.markRevealed(card.number));
                },

                resetGame() {
                    this.revealedCount = 0;
                    this.revealedNumbers = [];
                    // Re-init all canvases
                    document.querySelectorAll('[id^="canvas-"]').forEach((canvas, i) => {
                        const comp = Alpine.$data(canvas.closest('[x-data]'));
                        if (comp && comp.resetCard) comp.resetCard();
                    });
                    location.reload();
                }
            }
        }

        function scratchCard(index) {
            return {
                revealed: false,
                scratching: false,
                canvas: null,
                ctx: null,
                cardData: gameCards[index],
                lastX: 0,
                lastY: 0,

                init() {
                    this.$nextTick(() => {
                        this.canvas = document.getElementById(`canvas-${index}`);
                        if (!this.canvas) return;
                        this.resizeCanvas();
                        this.drawOverlay();
                    });
                },

                resizeCanvas() {
                    const rect = this.canvas.getBoundingClientRect();
                    this.canvas.width  = rect.width  || this.canvas.offsetWidth;
                    this.canvas.height = rect.height || this.canvas.offsetHeight;
                    this.ctx = this.canvas.getContext('2d');
                },

                drawOverlay() {
                    const ctx = this.ctx;
                    const w   = this.canvas.width;
                    const h   = this.canvas.height;

                    // Gradient background for scratch layer
                    const grad = ctx.createLinearGradient(0, 0, w, h);
                    grad.addColorStop(0, '#4c1d95');
                    grad.addColorStop(0.5, '#831843');
                    grad.addColorStop(1, '#1e1b4b');
                    ctx.fillStyle = grad;
                    ctx.roundRect(0, 0, w, h, 16);
                    ctx.fill();

                    // Scratch hint text
                    ctx.save();
                    ctx.fillStyle = 'rgba(255,255,255,0.15)';
                    ctx.font = `bold ${Math.floor(w * 0.12)}px Arial`;
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText('🪙 خربش هنا', w / 2, h / 2);
                    ctx.restore();

                    // Coin pattern dots
                    ctx.fillStyle = 'rgba(255,255,255,0.05)';
                    for (let x = 20; x < w; x += 25) {
                        for (let y = 20; y < h; y += 25) {
                            ctx.beginPath();
                            ctx.arc(x, y, 2, 0, Math.PI * 2);
                            ctx.fill();
                        }
                    }
                },

                getPos(e) {
                    const rect = this.canvas.getBoundingClientRect();
                    const scaleX = this.canvas.width  / rect.width;
                    const scaleY = this.canvas.height / rect.height;
                    if (e.touches) {
                        return {
                            x: (e.touches[0].clientX - rect.left) * scaleX,
                            y: (e.touches[0].clientY - rect.top)  * scaleY,
                        };
                    }
                    return {
                        x: (e.clientX - rect.left) * scaleX,
                        y: (e.clientY - rect.top)  * scaleY,
                    };
                },

                startScratch(e) {
                    if (this.revealed) return;
                    this.scratching = true;
                    const pos = this.getPos(e);
                    this.lastX = pos.x;
                    this.lastY = pos.y;
                    this.eraseDot(pos.x, pos.y);
                },

                doScratch(e) {
                    if (!this.scratching || this.revealed) return;
                    const pos = this.getPos(e);
                    this.eraseLine(this.lastX, this.lastY, pos.x, pos.y);
                    this.lastX = pos.x;
                    this.lastY = pos.y;
                    this.checkReveal();
                },

                stopScratch() {
                    this.scratching = false;
                    if (!this.revealed) this.checkReveal();
                },

                eraseDot(x, y) {
                    this.ctx.globalCompositeOperation = 'destination-out';
                    this.ctx.beginPath();
                    this.ctx.arc(x, y, 28, 0, Math.PI * 2);
                    this.ctx.fill();
                    this.ctx.globalCompositeOperation = 'source-over';
                },

                eraseLine(x1, y1, x2, y2) {
                    this.ctx.globalCompositeOperation = 'destination-out';
                    this.ctx.lineWidth   = 56;
                    this.ctx.lineCap     = 'round';
                    this.ctx.lineJoin    = 'round';
                    this.ctx.beginPath();
                    this.ctx.moveTo(x1, y1);
                    this.ctx.lineTo(x2, y2);
                    this.ctx.stroke();
                    this.ctx.globalCompositeOperation = 'source-over';
                },

                checkReveal() {
                    const imageData = this.ctx.getImageData(0, 0, this.canvas.width, this.canvas.height);
                    const pixels    = imageData.data;
                    let transparent = 0;
                    for (let i = 3; i < pixels.length; i += 4) {
                        if (pixels[i] < 128) transparent++;
                    }
                    const pct = transparent / (pixels.length / 4);
                    if (pct > 0.55) {
                        this.autoReveal();
                    }
                },

                autoReveal() {
                    if (this.revealed) return;
                    this.revealed = true;
                    // Smooth fade out the remaining overlay
                    let alpha = 1;
                    const fade = () => {
                        alpha -= 0.08;
                        if (alpha <= 0) {
                            this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
                            return;
                        }
                        this.ctx.globalAlpha = alpha;
                        this.ctx.globalCompositeOperation = 'source-over';
                        // Don't redraw — just reduce opacity
                        this.canvas.style.opacity = alpha;
                        requestAnimationFrame(fade);
                    };
                    fade();
                    // Notify parent
                    if (globalAlpineRoot) {
                        globalAlpineRoot.markRevealed(this.cardData.number);
                    }
                },

                resetCard() {
                    this.revealed   = false;
                    this.scratching = false;
                    this.canvas.style.opacity = 1;
                    this.ctx.globalAlpha = 1;
                    this.ctx.globalCompositeOperation = 'source-over';
                    this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
                    this.drawOverlay();
                }
            }
        }
    </script>
    @endpush

</x-app-layout>
