@props([
    'title' => '',
    'icon' => '',
    'tabs' => [],
    'sectionId' => '',
])

@php
    $firstTabId = count($tabs) > 0 ? $tabs[0]['id'] : '';
@endphp

<div id="secao-{{ $sectionId }}"
     class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden"
     x-data="{
        activeTab: null,
        init() {
            try {
                this.activeTab = localStorage.getItem('dashboard_tab_{{ $sectionId }}') || '{{ $firstTabId }}';
            } catch(e) {
                this.activeTab = '{{ $firstTabId }}';
            }
        },
        selectTab(tabId) {
            this.activeTab = tabId;
            try {
                localStorage.setItem('dashboard_tab_{{ $sectionId }}', tabId);
            } catch(e) {}
        }
     }">

    {{-- Header --}}
    <div class="px-4 py-3 border-b border-gray-200 bg-gradient-to-r from-gray-50 to-white">
        <div class="flex items-center gap-2.5 mb-2.5">
            @if($icon)
            <div class="w-7 h-7 rounded-lg bg-gray-700 flex items-center justify-center">
                <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
                </svg>
            </div>
            @endif
            <h3 class="text-sm font-semibold text-gray-900">{{ $title }}</h3>
        </div>

        {{-- Tab Navigation --}}
        <div class="flex gap-1 overflow-x-auto scrollbar-none -mb-px">
            @foreach($tabs as $tab)
                <button type="button"
                        @click="selectTab('{{ $tab['id'] }}')"
                        :class="activeTab === '{{ $tab['id'] }}'
                            ? 'bg-white border-gray-300 border-b-white text-gray-900 shadow-sm'
                            : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50 border-transparent'"
                        class="relative px-3 py-1.5 text-[11px] font-medium rounded-t-lg border transition whitespace-nowrap flex items-center gap-1.5">
                    {{ $tab['label'] }}
                    @if(isset($tab['badge']) && $tab['badge'] > 0)
                        <span class="text-[9px] px-1.5 py-0.5 rounded-full font-bold leading-none {{ isset($tab['urgent']) && $tab['urgent'] ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ $tab['badge'] }}
                        </span>
                    @endif
                </button>
            @endforeach
        </div>
    </div>

    {{-- Tab Content Panels --}}
    <div class="min-h-[120px]">
        {{ $slot }}
    </div>
</div>
