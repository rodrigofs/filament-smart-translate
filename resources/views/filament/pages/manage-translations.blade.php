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

        {{-- Translations Table --}}
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
