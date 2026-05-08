<x-app-layout>
    <x-slot name="title">من نحن</x-slot>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="text-center mb-12">
            <div class="text-6xl mb-4">🎮</div>
            <h1 class="text-4xl font-black">من نحن</h1>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-8 prose prose-invert max-w-none">
            <div class="text-gray-300 leading-relaxed text-lg whitespace-pre-wrap">{{ $text }}</div>
        </div>
    </div>
</x-app-layout>
