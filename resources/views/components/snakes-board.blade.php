<div class="sl-board" aria-label="لوحة السلم والتعبان: ١٠٠ خانة">
    <div class="sl-board-grid">
        <template x-for="number in boardNumbers" :key="number">
            <button type="button" class="sl-cell" :class="cellClass(number)" @click="inspect(number)" :aria-label="cellLabel(number)">
                <span class="sl-cell-number" x-text="number"></span>
                <span class="sl-cell-icon" aria-hidden="true" x-text="icon(number)"></span>
            </button>
        </template>
    </div>
    <div x-show="showPaths" class="sl-paths" aria-hidden="true">
        <template x-for="link in links" :key="link.from">
            <svg viewBox="0 0 100 100" style="position:absolute;inset:0;width:100%;height:100%">
                <g x-show="link.to > link.from"><path :d="ladder(link)" fill="none" stroke="#fff9e9" stroke-width="1.25" stroke-linecap="round"/><path :d="ladder(link)" fill="none" stroke="#b37b30" stroke-width=".65" stroke-linecap="round"/></g>
                <g x-show="link.to < link.from">
                <path :d="snake(link)" fill="none" stroke="#fff9e9" stroke-width="2.65" stroke-linecap="round"/>
                <path :d="snake(link)" fill="none" stroke="#647b65" stroke-width="1.85" stroke-linecap="round"/>
                <path :d="snake(link)" fill="none" stroke="#d9e2bc" stroke-width=".45" stroke-dasharray=".5 1.3" stroke-linecap="round"/>
                <ellipse :cx="point(link.from).x" :cy="point(link.from).y" rx="1.9" ry="1.6" fill="#647b65" stroke="#fff9e9" stroke-width=".3"/>
                <circle :cx="point(link.from).x - .7" :cy="point(link.from).y - .4" r=".28" fill="#fff"/>
                <circle :cx="point(link.from).x + .7" :cy="point(link.from).y - .4" r=".28" fill="#fff"/>
                </g>
            </svg>
        </template>
    </div>
    <template x-for="(player, index) in players" :key="index">
        <div x-show="displayPositions[index] > 0" class="sl-token" :class="index === 0 ? 'sl-token-blue' : 'sl-token-rose'" :style="tokenStyle(index)" :aria-label="player.name + ' في الخانة ' + displayPositions[index]" role="img"><span x-text="index + 1"></span></div>
    </template>
</div>
