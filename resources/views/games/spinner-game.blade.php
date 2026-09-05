<x-app-layout>
    <x-slot name="title">{{ $game->name }}</x-slot>
    <x-game-comfort-note />

    <div class="max-w-2xl mx-auto px-4 py-8" x-data="spinnerGame()">

        <!-- Header -->
        <div class="text-center mb-8">
            <a href="{{ route('games.show', $game->slug) }}" class="text-gray-500 hover:text-white text-sm">← العودة</a>
            <h1 class="text-3xl font-black mt-3">{{ $game->name }}</h1>
            <p class="text-gray-400 text-sm mt-1">اضغط "لف" وانتظر النتيجة!</p>
        </div>

        @if ($images->isEmpty())
            <div class="text-center py-20 text-gray-500">
                <div class="text-5xl mb-4">🎡</div>
                <p>لا توجد صور في العجلة بعد</p>
            </div>
        @else
            <!-- Spinner Canvas -->
            <div class="relative mx-auto mb-8" style="width: 320px; height: 320px;">
                <!-- Canvas for wheel -->
                <canvas id="wheelCanvas" width="320" height="320" class="rounded-full shadow-2xl"></canvas>

                <!-- Center Button -->
                <div class="absolute inset-0 flex items-center justify-center">
                    <button @click="spin()" :disabled="isSpinning"
                        class="w-20 h-20 rounded-full bg-white shadow-2xl flex flex-col items-center justify-center transition-all hover:scale-110 active:scale-95 disabled:opacity-70 disabled:cursor-not-allowed z-10">
                        <div class="text-2xl" x-text="isSpinning ? '⏳' : '🎯'"></div>
                        <div class="text-xs font-black text-gray-800 mt-1" x-text="isSpinning ? '...' : 'لف'"></div>
                    </button>
                </div>

                <!-- Pointer -->
                <div class="absolute top-0 left-1/2 -translate-x-1/2 -translate-y-2 z-20">
                    <div
                        class="w-0 h-0 border-l-[12px] border-r-[12px] border-b-[24px] border-l-transparent border-r-transparent border-b-white drop-shadow-lg">
                    </div>
                </div>
            </div>

            <!-- Result -->
            <div x-show="result" x-cloak
                class="bg-gray-900 border border-purple-700/50 rounded-3xl p-8 text-center mb-6"
                x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 scale-75"
                x-transition:enter-end="opacity-100 scale-100">
                <div class="text-lg text-gray-400 mb-2">نتيجة الدوران</div>
                <div class="text-5xl font-black" :style="`color: ${result?.color}`" x-text="result?.name"></div>
                @if (count($images) > 0)
                    <template x-if="result?.image">
                    <img x-bind:src="result.image" alt=""
                        class="w-32 h-32 object-cover rounded-2xl mx-auto mt-4 border-4 border-white/20">
                    </template>
                @endif
            </div>

            <!-- Spin History -->
            <div x-show="history.length > 0" x-cloak class="bg-gray-900 border border-gray-800 rounded-2xl p-5">
                <h3 class="font-bold mb-3 text-sm text-gray-400">آخر النتائج</h3>
                <div class="flex flex-wrap gap-2">
                    <template x-for="(item, i) in history.slice().reverse().slice(0, 8)" :key="i">
                        <div class="text-xs px-3 py-1 rounded-full font-bold"
                            :style="`background-color: ${item.color}20; color: ${item.color}; border: 1px solid ${item.color}40`"
                            x-text="item.name"></div>
                    </template>
                </div>
            </div>

        @endif
    </div>

    @push('scripts')
        <script>
            const spinnerItems = @json($spinnerData);

            function spinnerGame() {
                return {
                    items: spinnerItems,
                    isSpinning: false,
                    result: null,
                    history: [],
                    currentAngle: 0,
                    canvas: null,
                    ctx: null,

                    init() {
                        this.$nextTick(() => {
                            this.canvas = document.getElementById('wheelCanvas');
                            if (this.canvas) {
                                this.ctx = this.canvas.getContext('2d');
                                this.drawWheel(0);
                            }
                        });
                    },

                    drawWheel(rotation) {
                        if (!this.ctx || !this.items.length) return;

                        const ctx = this.ctx;
                        const canvas = this.canvas;
                        const cx = canvas.width / 2;
                        const cy = canvas.height / 2;
                        const r = cx - 4;
                        const n = this.items.length;
                        const arc = (2 * Math.PI) / n;

                        ctx.clearRect(0, 0, canvas.width, canvas.height);

                        this.items.forEach((item, i) => {
                            const startAngle = rotation + i * arc - Math.PI / 2;
                            const endAngle = startAngle + arc;

                            // Slice
                            ctx.beginPath();
                            ctx.moveTo(cx, cy);
                            ctx.arc(cx, cy, r, startAngle, endAngle);
                            ctx.closePath();
                            ctx.fillStyle = item.color;
                            ctx.fill();
                            ctx.strokeStyle = 'rgba(0,0,0,0.3)';
                            ctx.lineWidth = 2;
                            ctx.stroke();

                            // Text
                            ctx.save();
                            ctx.translate(cx, cy);
                            ctx.rotate(startAngle + arc / 2);
                            ctx.textAlign = 'right';
                            ctx.fillStyle = '#fff';
                            ctx.font = `bold ${Math.max(10, Math.min(14, 80 / n))}px Tajawal, sans-serif`;
                            ctx.shadowColor = 'rgba(0,0,0,0.5)';
                            ctx.shadowBlur = 3;
                            ctx.fillText(item.name, r - 10, 5);
                            ctx.restore();
                        });

                        // Center circle
                        ctx.beginPath();
                        ctx.arc(cx, cy, 28, 0, 2 * Math.PI);
                        ctx.fillStyle = '#1f2937';
                        ctx.fill();
                        ctx.strokeStyle = 'rgba(255,255,255,0.2)';
                        ctx.lineWidth = 3;
                        ctx.stroke();
                    },

                    spin() {
                        if (this.isSpinning || !this.items.length) return;

                        this.isSpinning = true;
                        this.result = null;

                        const winnerIdx = Math.floor(Math.random() * this.items.length);

                        const n = this.items.length;
                        const arc = (2 * Math.PI) / n;

                        // عدد لفات كاملة
                        const extraSpins = (5 + Math.floor(Math.random() * 3)) * 2 * Math.PI;

                        // نخلي السهم يقف في منتصف الجزء الفائز
                        const targetAngle =
                            extraSpins +
                            (2 * Math.PI - (winnerIdx * arc + arc / 2));

                        const startAngle = this.currentAngle;
                        const endAngle = startAngle + extraSpins +
                            ((targetAngle - startAngle) % (2 * Math.PI) + 2 * Math.PI) % (2 * Math.PI);

                        const duration = 5000;
                        const startTime = performance.now();

                        const animate = (now) => {
                            const elapsed = now - startTime;
                            const progress = Math.min(elapsed / duration, 1);

                            // easing
                            const ease = 1 - Math.pow(1 - progress, 4);

                            const angle = startAngle + (endAngle - startAngle) * ease;

                            this.currentAngle = angle;

                            // IMPORTANT
                            this.drawWheel(angle);

                            if (progress < 1) {
                                requestAnimationFrame(animate);
                            } else {
                                this.isSpinning = false;
                                this.result = this.items[winnerIdx];
                                this.history.push(this.result);
                                this.playSound();
                            }
                        };

                        requestAnimationFrame(animate);
                    },

                    playSound() {
                        try {
                            const ctx = new(window.AudioContext || window.webkitAudioContext)();
                            const osc = ctx.createOscillator();
                            const gain = ctx.createGain();
                            osc.connect(gain);
                            gain.connect(ctx.destination);
                            osc.frequency.setValueAtTime(880, ctx.currentTime);
                            osc.frequency.exponentialRampToValueAtTime(440, ctx.currentTime + 0.3);
                            gain.gain.setValueAtTime(0.3, ctx.currentTime);
                            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.5);
                            osc.start();
                            osc.onended = () => ctx.close();
                            osc.stop(ctx.currentTime + 0.5);
                        } catch (e) {}
                    }
                }
            }
        </script>
    @endpush
</x-app-layout>
