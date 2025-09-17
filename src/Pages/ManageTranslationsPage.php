<?php

declare(strict_types=1);

namespace Rodrigofs\FilamentSmartTranslate\Pages;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Rodrigofs\FilamentSmartTranslate\Data\TranslationData;
use Rodrigofs\FilamentSmartTranslate\Services\TranslationService;

class ManageTranslationsPage extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static string|null|\BackedEnum $navigationIcon = 'heroicon-o-language';
    protected static ?string $navigationLabel = 'Traduções';
    protected static ?string $title = 'Gerenciar Traduções';

    public Collection $translations;
    public string $locale;
    public array $statistics = [];
    public int $refreshCounter = 0;

    private ?TranslationService $translationService = null;

    public function mount(): void
    {
        $this->locale = 'all'; // Start with all locales visible
        $this->loadTranslations();
        $this->loadStatistics();
    }

    private function getTranslationService(): TranslationService
    {
        if ($this->translationService === null) {
            $this->translationService = app(TranslationService::class);
        }

        return $this->translationService;
    }

    public function getTableRecordKey(array|Model $record): string
    {
        return $record['key'];
    }

    public function getView(): string
    {
        return 'filament-smart-translate::filament.pages.manage-translations';
    }

    protected function loadTranslations(): void
    {
        $allTranslations = collect();

        // Load translations for all available locales
        foreach ($this->getTranslationService()->getAvailableLocales() as $locale) {
            $translations = $this->getTranslationService()->loadTranslations($locale);

            $localeTranslations = $translations->map(function ($value, $key) use ($locale) {
                $data = new TranslationData($key, $value, $locale);

                return [
                    'key' => $data->key,
                    'value' => $data->value,
                    'locale' => $data->locale,
                    'category' => $data->getCategory(),
                    'length' => $data->getLength(),
                    'is_empty' => $data->isEmpty(),
                    'is_long' => $data->isLong(),
                ];
            });

            $allTranslations = $allTranslations->merge($localeTranslations->values());
        }

        // If a specific locale is selected, filter to show only that locale
        if ($this->locale && $this->locale !== 'all') {
            $allTranslations = $allTranslations->filter(fn($translation) => $translation['locale'] === $this->locale);
        }

        $this->translations = $allTranslations;
    }

    protected function loadStatistics(): void
    {
        if ($this->locale === 'all') {
            // Calculate combined statistics for all locales
            $totalStats = [
                'total' => 0,
                'empty' => 0,
                'long' => 0,
                'average_length' => 0
            ];

            $allLengths = [];
            foreach ($this->getTranslationService()->getAvailableLocales() as $locale) {
                $stats = $this->getTranslationService()->getStatistics($locale);
                $totalStats['total'] += $stats['total'];
                $totalStats['empty'] += $stats['empty'];
                $totalStats['long'] += $stats['long'];

                // Collect all lengths for average calculation
                $translations = $this->getTranslationService()->loadTranslations($locale);
                $allLengths = array_merge($allLengths, $translations->map(fn($value) => strlen($value))->toArray());
            }

            $totalStats['average_length'] = count($allLengths) > 0 ? round(array_sum($allLengths) / count($allLengths), 2) : 0;
            $this->statistics = $totalStats;
        } else {
            $this->statistics = $this->getTranslationService()->getStatistics($this->locale);
        }
    }

    public function refreshTable(): void
    {
        // Clear any cache in the translation service
        $this->getTranslationService()->clearCache();

        // Reload data
        $this->loadTranslations();
        $this->loadStatistics();

        // Increment refresh counter to trigger reactivity
        $this->refreshCounter++;
    }

    public function refreshData(): void
    {
        $this->refreshTable();
    }

    public function updated($propertyName): void
    {
        // Refresh data when refreshCounter changes
        if ($propertyName === 'refreshCounter') {
            // This will cause Livewire to re-render the component
        }
    }

    public function table(Table $table): Table
    {
        return $table
            ->records(function (int $page, int $recordsPerPage, ?string $search = null): LengthAwarePaginator {
                // Use refreshCounter to ensure we get fresh data
                $_ = $this->refreshCounter;
                $translations = $this->translations;

                // Apply search filter
                if ($search) {
                    $translations = $translations->filter(function ($translation) use ($search) {
                        return str_contains(strtolower($translation['key']), strtolower($search)) ||
                               str_contains(strtolower($translation['value']), strtolower($search)) ||
                               str_contains(strtolower($translation['category']), strtolower($search));
                    });
                }

                $skip = ($page - 1) * $recordsPerPage;

                return new LengthAwarePaginator(
                    items: $translations->slice($skip, $recordsPerPage),
                    total: $translations->count(),
                    perPage: $recordsPerPage,
                    currentPage: $page,
                    options: ['path' => request()->url(), 'pageName' => 'page']
                );
            })
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->label('Translation Key')
                    ->sortable()
                    ->searchable()
                    ->copyable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('value')
                    ->label('Translation Value')
                    ->sortable()
                    ->searchable()
                    ->limit(100)
                    ->wrap(),

                Tables\Columns\TextColumn::make('locale')
                    ->label('Locale')
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('category')
                    ->label('Category')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'auth' => 'info',
                        'validation' => 'warning',
                        'error' => 'danger',
                        'ui' => 'success',
                        'resources' => 'primary',
                        'navigations' => 'secondary',
                        'actions' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('length')
                    ->label('Length')
                    ->sortable()
                    ->alignCenter()
                    ->color(fn(int $state): string => $state > 100 ? 'warning' : 'gray'),

                Tables\Columns\IconColumn::make('is_empty')
                    ->label('Empty')
                    ->boolean()
                    ->alignCenter(),
            ])
            ->headerActions([
                Action::make('add_translation')
                    ->label('New Translation')
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->schema([
                        Select::make('locale')
                            ->label('Language')
                            ->options(array_combine(
                                $this->getTranslationService()->getAvailableLocales(),
                                $this->getTranslationService()->getAvailableLocales()
                            ))
                            ->default($this->locale)
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function ($state, callable $set) {
                                // Clear the key field when locale changes to revalidate
                                $set('key', '');
                            }),

                        TextInput::make('key')
                            ->label('Key')
                            ->required()
                            ->rules([
                                'string',
                                'max:255',
                                function ($attribute, $value, $fail) {
                                    $locale = request()->input('mountedActionData.0.locale', $this->locale);
                                    $translations = $this->getTranslationService()->loadTranslations($locale);
                                    if ($translations->has($value)) {
                                        $fail('This key already exists for this locale.');
                                    }
                                },
                            ])
                            ->placeholder('e.g: resources.user'),

                        Textarea::make('value')
                            ->label('Translation')
                            ->required()
                            ->maxLength(1000)
                            ->rows(3),
                    ])
                    ->action(function (array $data) {
                        $this->addTranslation($data);
                    })
                    ->after(function () {
                        $this->refreshData();
                    })
                    ->successNotificationTitle('Translation added successfully!'),
            ])
            ->recordActions([
                Action::make('edit')
                    ->label('Edit')
                    ->icon('heroicon-o-pencil')
                    ->form([
                        Select::make('locale')
                            ->label('Language')
                            ->options(array_combine(
                                $this->getTranslationService()->getAvailableLocales(),
                                $this->getTranslationService()->getAvailableLocales()
                            ))
                            ->required()
                            ->reactive(),

                        TextInput::make('key')
                            ->label('Key')
                            ->required()
                            ->disabled()
                            ->dehydrated(false),

                        Textarea::make('value')
                            ->label('Translation')
                            ->required()
                            ->maxLength(1000)
                            ->rows(3),
                    ])
                    ->fillForm(fn (array $record): array => [
                        'locale' => $record['locale'],
                        'key' => $record['key'],
                        'value' => $record['value'],
                    ])
                    ->action(function (array $data, array $record): void {
                        $this->updateTranslation($record['locale'], $record['key'], $data['value'], $data['locale']);
                    })
                    ->after(function () {
                        $this->refreshData();
                    })
                    ->successNotificationTitle('Translation updated successfully!'),

                Action::make('delete')
                    ->label('Delete')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Delete Translation')
                    ->modalDescription('Are you sure you want to delete this translation? This action cannot be undone.')
                    ->action(function (array $record): void {
                        $this->deleteTranslation($record['locale'], $record['key']);
                    })
                    ->after(function () {
                        $this->refreshData();
                    })
                    ->successNotificationTitle('Translation deleted successfully!'),


            ])
            ->toolbarActions([
                \Filament\Actions\BulkAction::make('delete')
                    ->label('Delete Selected')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Delete Selected Translations')
                    ->modalDescription('Are you sure you want to delete the selected translations? This action cannot be undone.')
                    ->action(function (Collection $records) {
                        $this->bulkDeleteTranslations($records->toArray());
                    })
                    ->after(function () {
                        $this->refreshData();
                    })
                    ->successNotificationTitle('Translations deleted successfully!'),
            ])
            ->emptyStateIcon('heroicon-o-language')
            ->emptyStateHeading('No translations found')
            ->emptyStateDescription('Start by adding a new translation using the button above.')
            ->striped()
            ->defaultSort('key');
    }


    public function addTranslation(array $data): void
    {
        try {
            $locale = $data['locale'] ?? $this->locale;

            $this->getTranslationService()->addTranslation($locale, $data['key'], $data['value']);

            Notification::make()
                ->title('Translation added')
                ->body("The translation '{$data['key']}' was added successfully for locale '{$locale}'.")
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error adding translation')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function updateTranslation(string $originalLocale, string $key, string $value, string $newLocale): void
    {
        try {
            // If locale changed, delete from original and add to new locale
            if ($originalLocale !== $newLocale) {
                $this->getTranslationService()->deleteTranslation($originalLocale, $key);
                $this->getTranslationService()->addTranslation($newLocale, $key, $value);
            } else {
                $this->getTranslationService()->updateTranslation($originalLocale, $key, $value);
            }

            Notification::make()
                ->title('Translation updated')
                ->body("The translation '{$key}' was updated successfully.")
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error updating translation')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function deleteTranslation(string $locale, string $key): void
    {
        try {
            $this->getTranslationService()->deleteTranslation($locale, $key);

            Notification::make()
                ->title('Translation deleted')
                ->body("The translation '{$key}' was deleted successfully from locale '{$locale}'.")
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error deleting translation')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function bulkDeleteTranslations(array $records): void
    {
        try {
            $groupedByLocale = collect($records)->groupBy('locale');
            $totalDeleted = 0;
            $totalFailed = 0;

            foreach ($groupedByLocale as $locale => $translations) {
                $keys = $translations->pluck('key')->toArray();
                $result = $this->getTranslationService()->bulkDeleteTranslations($locale, $keys);

                $successful = count($keys) - count($result);
                $failed = count($result);

                $totalDeleted += $successful;
                $totalFailed += $failed;
            }

            Notification::make()
                ->title('Translations deleted')
                ->body("Deleted {$totalDeleted} translations successfully.")
                ->success()
                ->send();

            if ($totalFailed > 0) {
                Notification::make()
                    ->title('Some translations were not deleted')
                    ->body("Failed to delete {$totalFailed} translations.")
                    ->warning()
                    ->send();
            }

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error deleting translations')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getActions(): array
    {
        return [
            Action::make('change_locale')
                ->label('Change Language')
                ->icon('heroicon-o-globe-alt')
                ->form([
                    Select::make('locale')
                        ->label('Select Language')
                        ->options(array_merge(
                            ['all' => 'All Locales'],
                            array_combine(
                                $this->getTranslationService()->getAvailableLocales(),
                                $this->getTranslationService()->getAvailableLocales()
                            )
                        ))
                        ->default($this->locale)
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->locale = $data['locale'];
                    $this->refreshData();

                    $localeDisplay = $this->locale === 'all' ? 'all locales' : $this->locale;
                    Notification::make()
                        ->title('Language changed')
                        ->body("Displaying translations for: {$localeDisplay}")
                        ->success()
                        ->send();
                }),

            Action::make('refresh')
                ->label('Refresh')
                ->icon('heroicon-o-arrow-path')
                ->action(function () {
                    $this->refreshData();

                    Notification::make()
                        ->title('Translations refreshed')
                        ->success()
                        ->send();
                }),

            Action::make('export')
                ->label('Export')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    if ($this->locale === 'all') {
                        // Export all locales as a zip file
                        return $this->exportAllLocales();
                    } else {
                        $data = $this->getTranslationService()->exportTranslations($this->locale);

                        return response()->streamDownload(function () use ($data) {
                            echo json_encode(
                                $data,
                                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                            );
                        }, "translations-{$this->locale}.json");
                    }
                })
                ->color('gray'),

            Action::make('backup')
                ->label('Create Backup')
                ->icon('heroicon-o-shield-check')
                ->action(function () {
                    try {
                        if ($this->locale === 'all') {
                            $backupPaths = [];
                            foreach ($this->getTranslationService()->getAvailableLocales() as $locale) {
                                $backupPaths[] = $this->getTranslationService()->createBackup($locale);
                            }
                            $pathsText = implode(', ', $backupPaths);

                            Notification::make()
                                ->title('Backups created')
                                ->body("Backups saved for all locales: {$pathsText}")
                                ->success()
                                ->send();
                        } else {
                            $backupPath = $this->getTranslationService()->createBackup($this->locale);

                            Notification::make()
                                ->title('Backup created')
                                ->body("Backup saved at: {$backupPath}")
                                ->success()
                                ->send();
                        }
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Error creating backup')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->color('warning'),

            Action::make('statistics')
                ->label('Statistics')
                ->icon('heroicon-o-chart-bar')
                ->schema([
                    \Filament\Infolists\Components\TextEntry::make('total')
                        ->label('Total Translations')
                        ->state($this->statistics['total'] ?? 0),
                    \Filament\Infolists\Components\TextEntry::make('empty')
                        ->label('Empty Translations')
                        ->state($this->statistics['empty'] ?? 0),
                    \Filament\Infolists\Components\TextEntry::make('long')
                        ->label('Long Translations (>100 chars)')
                        ->state($this->statistics['long'] ?? 0),
                    \Filament\Infolists\Components\TextEntry::make('average_length')
                        ->label('Average Length')
                        ->state($this->statistics['average_length'] ?? 0),
                ])
                ->modalHeading('Translation Statistics')
                ->color('info'),
        ];
    }

    protected function exportAllLocales()
    {
        $allTranslations = [];

        foreach ($this->getTranslationService()->getAvailableLocales() as $locale) {
            $translations = $this->getTranslationService()->exportTranslations($locale);
            $allTranslations[$locale] = $translations;
        }

        return response()->streamDownload(function () use ($allTranslations) {
            echo json_encode(
                $allTranslations,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );
        }, "translations-all-locales.json");
    }
}
