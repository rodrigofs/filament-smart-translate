@php

$translations = $this->translations;
$locale = $this->locale;
$count = $this->translations->count();


@endphp
<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Ações Principais --}}
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                        Gerenciamento de Traduções
                    </h3>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        Gerencie todas as traduções do sistema de forma eficiente
                    </p>
                </div>

                <div class="flex space-x-2">
{{--                    {{ ($this->getAction('refresh'))() }}--}}
{{--                    {{ ($this->getAction('export_all'))() }}--}}
{{--                    {{ ($this->getAction('statistics'))() }}--}}
                </div>
            </div>
        </div>

        {{-- Tabela de traduções --}}
        <div class="bg-white dark:bg-gray-800 shadow rounded-lg">
            {{ $this->table }}
        </div>
    </div>
</x-filament-panels::page>
