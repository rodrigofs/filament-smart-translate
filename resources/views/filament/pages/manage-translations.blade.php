@php
$translations = $this->translations;
$locale = $this->locale;
$count = $this->translations->count();
@endphp

<x-filament-panels::page>
    <div
        class="space-y-6"
        x-data="{
            refreshCounter: @entangle('refreshCounter'),
            init() {
                this.$watch('refreshCounter', () => {
                    // Force component refresh when counter changes
                    if (window.Livewire) {
                        this.$wire.$refresh();
                    }
                });
            }
        }"
    >
        {{-- Main Actions --}}
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        Translation Management
                    </h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Manage all system translations efficiently
                    </p>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                        Current locale: {{ $locale === 'all' ? 'All Locales' : $locale }} |
                        Total translations: {{ $count }}
                    </p>
                </div>

                <div class="flex space-x-2">
                    <button
                        wire:click="refreshData"
                        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                    >
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                        </svg>
                        Refresh
                    </button>
                </div>
            </div>
        </div>

        {{-- Translations Table --}}
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
