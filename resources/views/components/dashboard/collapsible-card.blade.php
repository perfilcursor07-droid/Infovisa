@props([
    'title' => '',
    'icon' => '',
    'count' => 0,
    'cardId' => '',
])

<div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
     x-data="{
        expanded: false,
        init() {
            try {
                const stored = localStorage.getItem('dashboard_card_{{ $cardId }}');
                if (stored !== null) {
                    this.expanded = stored === 'true';
                }
            } catch(e) {
                this.expanded = false;
            }
        },
        toggle() {
            this.expanded = !this.expanded;
            try {
                localStorage.setItem('dashboard_card_{{ $cardId }}', this.expanded);
            } catch(e) {}
        }
     }">

    {{-- Header (always visible) --}}
    <button type="button"
            @click="toggle()"
            class="w-full flex items-center gap-3 px-4 py-2.5 hover:bg-gray-50/50 transition text-left">
        @if($icon)
        <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
            <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
            </svg>
        </div>
        @endif
        <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-gray-700">{{ $title }}</p>
        </div>
        @if($count > 0)
        <span class="text-xs px-2 py-0.5 bg-gray-100 text-gray-600 rounded-full font-bold">{{ $count }}</span>
        @endif
        <svg class="w-4 h-4 text-gray-300 transition-transform duration-200"
             :class="expanded ? 'rotate-90' : ''"
             fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
        </svg>
    </button>

    {{-- Collapsible Content --}}
    <div x-show="expanded"
         x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-1"
         class="border-t border-gray-100">
        {{ $slot }}
    </div>
</div>
