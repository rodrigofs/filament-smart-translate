<?php

declare(strict_types=1);

namespace Rodrigofs\FilamentSmartTranslate\Pages;

use Exception;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Rodrigofs\FilamentSmartTranslate\Data\TranslationData;
use Rodrigofs\FilamentSmartTranslate\Services\TranslationService;
use Rodrigofs\FilamentSmartTranslate\Traits\Page\PageTranslateble;

class TranslationManagerPage extends Page implements Tables\Contracts\HasTable
{
    use PageTranslateble;
    use Tables\Concerns\InteractsWithTable;

    protected static ?string $slug = null;

    /** @var Collection<int, array{key: string, value: string, locale: string, category: string, length: int, is_long: bool}> */
    public Collection $translations;

    public string $locale;

    /** @var array<string, int> */
    public array $statistics = [];

    public int $refreshCounter = 0;

    private ?TranslationService $translationService = null;

    /**
     * Get translation for the Translation Manager Page
     *
     * @param  array<string, mixed>  $replace
     */
    protected function trans(string $key, array $replace = []): string
    {
        return __("filament-smart-translate::translation-manager.{$key}", $replace);
    }

    public static function getNavigationIcon(): ?string
    {
        return Config::get('filament-smart-translate.translation_page.navigation.icon', 'heroicon-o-language');
    }

    public static function getNavigationLabel(): string
    {
        return Config::get(
            'filament-smart-translate.translation_page.navigation.label',
            __('filament-smart-translate::translation-manager.navigation_label')
        );
    }

    public function getTitle(): string
    {
        return Config::get(
            'filament-smart-translate.translation_page.page.title',
            __('filament-smart-translate::translation-manager.page_title')
        );
    }

    public static function getNavigationGroup(): ?string
    {
        return Config::get('filament-smart-translate.translation_page.navigation.group');
    }

    public static function getNavigationSort(): ?int
    {
        return Config::get('filament-smart-translate.translation_page.navigation.sort');
    }

    public static function getSlug(?\Filament\Panel $panel = null): string
    {
        return Config::get('filament-smart-translate.translation_page.page.slug', 'translations');
    }

    public static function shouldRegisterNavigation(): bool
    {
        // Check if the page is enabled
        if (! Config::get('filament-smart-translate.translation_page.enabled', true)) {
            return false;
        }

        // Check if it should only be shown in development
        if (Config::get('filament-smart-translate.translation_page.dev_only', false)) {
            return app()->environment('local', 'development', 'testing');
        }

        return true;
    }

    public static function canAccess(): bool
    {
        // Check if the page is enabled
        if (! Config::get('filament-smart-translate.translation_page.enabled', true)) {
            return false;
        }

        // Check if it should only be shown in development
        if (Config::get('filament-smart-translate.translation_page.dev_only', false)) {
            if (! app()->environment('local', 'development', 'testing')) {
                return false;
            }
        }

        // Check custom authorization callback
        $authorize = Config::get('filament-smart-translate.translation_page.authorize');
        if (is_callable($authorize)) {
            return $authorize();
        }

        return true;
    }

    public function mount(): void
    {
        $this->locale = app()->getLocale(); // Start with current locale
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

    public function getTableRecordKey(array | Model $record): string
    {
        return $record['key'];
    }

    public function getView(): string
    {
        return 'filament-smart-translate::filament.pages.manage-translations';
    }

    protected function loadTranslations(): void
    {
        try {
            $translations = $this->getTranslationService()->loadTranslations($this->locale);

            $this->translations = $translations->map(function (string $value, string $key): array {

                $data = new TranslationData($key, $value, $this->locale);

                return [
                    'key' => $data->key,
                    'value' => $data->value,
                    'locale' => $data->locale,
                    'category' => $data->getCategory(),
                    'length' => $data->getLength(),
                    'is_long' => $data->isLong(),
                ];
            })->values();
        } catch (Exception $e) {
            // If there's an error loading translations, use empty collection
            $this->translations = collect();
        }
    }

    protected function loadStatistics(): void
    {
        try {
            $this->statistics = $this->getTranslationService()->getStatistics($this->locale);
        } catch (Exception $e) {
            // Fallback to empty statistics if locale is invalid
            $this->statistics = [
                'total' => 0,
                'empty' => 0,
                'long' => 0,
                'average_length' => 0,
            ];
        }
    }

    public function refreshTable(): void
    {
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

    public function updated(string $propertyName): void
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
                    ->label($this->trans('columns.key'))
                    ->sortable()
                    ->searchable()
                    ->copyable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('value')
                    ->label($this->trans('columns.value'))
                    ->sortable()
                    ->searchable()
                    ->limit(100)
                    ->wrap(),

                Tables\Columns\TextColumn::make('locale')
                    ->label($this->trans('columns.locale'))
                    ->badge()
                    ->color('info')
                    ->sortable(),

                Tables\Columns\TextColumn::make('category')
                    ->label($this->trans('columns.category'))
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
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
                    ->label($this->trans('columns.length'))
                    ->sortable()
                    ->alignCenter()
                    ->color(fn (int $state): string => $state > 100 ? 'warning' : 'gray'),

            ])
            ->headerActions([
                Action::make('add_translation')
                    ->label($this->trans('actions.new_translation'))
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->schema([
                        Select::make('locale')
                            ->label($this->trans('forms.language'))
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
                            ->label($this->trans('forms.key'))
                            ->required()
                            ->rules([
                                'string',
                                'max:255',
                                function ($attribute, $value, $fail) {
                                    $locale = request()->input('mountedActionData.0.locale', $this->locale);
                                    $translations = $this->getTranslationService()->loadTranslations($locale);
                                    if ($translations->has($value)) {
                                        $fail($this->trans('validation.key_already_exists'));
                                    }
                                },
                            ])
                            ->placeholder($this->trans('forms.key_placeholder')),

                        Textarea::make('value')
                            ->label($this->trans('forms.translation'))
                            ->required()
                            ->maxLength(1000)
                            ->rows(3),
                    ])
                    ->action(function (array $data) {
                        $this->addTranslation($data);
                    })
                    ->after(function () {
                        $this->refreshData();
                    }),
            ])
            ->recordActions([
                Action::make('edit')
                    ->label($this->trans('actions.edit'))
                    ->icon('heroicon-o-pencil')
                    ->form([
                        Select::make('locale')
                            ->label($this->trans('forms.language'))
                            ->options(array_combine(
                                $this->getTranslationService()->getAvailableLocales(),
                                $this->getTranslationService()->getAvailableLocales()
                            ))
                            ->required()
                            ->reactive(),

                        TextInput::make('key')
                            ->label($this->trans('forms.key'))
                            ->required()
                            ->disabled()
                            ->dehydrated(false),

                        Textarea::make('value')
                            ->label($this->trans('forms.translation'))
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
                    }),

                Action::make('delete')
                    ->label($this->trans('actions.delete'))
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading($this->trans('modals.delete_translation.heading'))
                    ->modalDescription($this->trans('modals.delete_translation.description'))
                    ->action(function (array $record): void {
                        $this->deleteTranslation($record['locale'], $record['key']);
                    })
                    ->after(function () {
                        $this->refreshData();
                    }),

            ])
            ->toolbarActions($this->getBulkActions())
            ->emptyStateIcon('heroicon-o-language')
            ->emptyStateHeading($this->trans('empty_state.heading'))
            ->emptyStateDescription($this->trans('empty_state.description'))
            ->striped()
            ->defaultSort('key');
    }

    /** @param array<string, string> $data */
    public function addTranslation(array $data): void
    {
        try {
            $locale = $data['locale'] ?? $this->locale;

            $this->getTranslationService()->addTranslation($locale, $data['key'], $data['value']);

            Notification::make()
                ->title($this->trans('notifications.translation_added.title'))
                ->body($this->trans('notifications.translation_added.body', [
                    'key' => $data['key'],
                    'locale' => $locale,
                ]))
                ->success()
                ->send();

        } catch (Exception $e) {
            Notification::make()
                ->title($this->trans('notifications.error_adding_translation.title'))
                ->body($this->trans('notifications.error_adding_translation.body', [
                    'message' => $e->getMessage(),
                ]))
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
                ->title($this->trans('notifications.translation_updated.title'))
                ->body($this->trans('notifications.translation_updated.body', [
                    'key' => $key,
                ]))
                ->success()
                ->send();

        } catch (Exception $e) {
            Notification::make()
                ->title($this->trans('notifications.error_updating_translation.title'))
                ->body($this->trans('notifications.error_updating_translation.body', [
                    'message' => $e->getMessage(),
                ]))
                ->danger()
                ->send();
        }
    }

    public function deleteTranslation(string $locale, string $key): void
    {
        try {
            $this->getTranslationService()->deleteTranslation($locale, $key);

            Notification::make()
                ->title($this->trans('notifications.translation_deleted.title'))
                ->body($this->trans('notifications.translation_deleted.body', [
                    'key' => $key,
                    'locale' => $locale,
                ]))
                ->success()
                ->send();

        } catch (Exception $e) {
            Notification::make()
                ->title($this->trans('notifications.error_deleting_translation.title'))
                ->body($this->trans('notifications.error_deleting_translation.body', [
                    'message' => $e->getMessage(),
                ]))
                ->danger()
                ->send();
        }
    }

    /** @param array<int, array{key: string, locale: string}> $records */
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
                ->title($this->trans('notifications.translations_deleted.title'))
                ->body($this->trans('notifications.translations_deleted.body', [
                    'count' => $totalDeleted,
                ]))
                ->success()
                ->send();

            if ($totalFailed > 0) {
                Notification::make()
                    ->title($this->trans('notifications.some_translations_not_deleted.title'))
                    ->body($this->trans('notifications.some_translations_not_deleted.body', [
                        'count' => $totalFailed,
                    ]))
                    ->warning()
                    ->send();
            }

        } catch (Exception $e) {
            Notification::make()
                ->title($this->trans('notifications.error_deleting_translations.title'))
                ->body($this->trans('notifications.error_deleting_translations.body', [
                    'message' => $e->getMessage(),
                ]))
                ->danger()
                ->send();
        }
    }

    /**
     * @return array<\Filament\Actions\BulkAction>
     */
    protected function getBulkActions(): array
    {
        $actions = [];

        if (Config::get('filament-smart-translate.translation_page.features.bulk_operations', true)) {
            $actions[] = \Filament\Actions\BulkAction::make('delete')
                ->label($this->trans('actions.delete_selected'))
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading($this->trans('modals.delete_selected_translations.heading'))
                ->modalDescription($this->trans('modals.delete_selected_translations.description'))
                ->action(function (Collection $records) {
                    $this->bulkDeleteTranslations($records->toArray());
                })
                ->after(function () {
                    $this->refreshData();
                });
        }

        return $actions;
    }

    protected function getActions(): array
    {
        $actions = [];

        // Locale selector action
        if (Config::get('filament-smart-translate.translation_page.features.locale_selector', true)) {
            $actions[] = Action::make('change_locale')
                ->label($this->trans('actions.change_language'))
                ->icon('heroicon-o-globe-alt')
                ->form([
                    Select::make('locale')
                        ->label($this->trans('forms.select_language'))
                        ->options(array_combine(
                            $this->getTranslationService()->getAvailableLocales(),
                            $this->getTranslationService()->getAvailableLocales()
                        ))
                        ->default($this->locale)
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->locale = $data['locale'];
                    $this->refreshData();

                    Notification::make()
                        ->title($this->trans('notifications.language_changed.title'))
                        ->body($this->trans('notifications.language_changed.body', [
                            'locale' => $this->locale,
                        ]))
                        ->success()
                        ->send();
                });
        }

        // Refresh action (always available)
        $actions[] = Action::make('refresh')
            ->label($this->trans('actions.refresh'))
            ->icon('heroicon-o-arrow-path')
            ->modal(false)
            ->action(function () {
                $this->refreshData();

                Notification::make()
                    ->title($this->trans('notifications.translations_refreshed.title'))
                    ->success()
                    ->send();
            });

        // Export action
        if (Config::get('filament-smart-translate.translation_page.features.export', true)) {
            $actions[] = Action::make('export')
                ->label($this->trans('actions.export'))
                ->modal(false)
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    $data = $this->getTranslationService()->exportTranslations($this->locale);

                    return response()->streamDownload(function () use ($data) {
                        echo json_encode(
                            $data,
                            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
                        );
                    }, "translations-{$this->locale}.json");
                })
                ->color('gray');
        }

        // Backup action
        if (Config::get('filament-smart-translate.translation_page.features.backup', true)) {
            $actions[] = Action::make('backup')
                ->label($this->trans('actions.create_backup'))
                ->modal(false)
                ->icon('heroicon-o-shield-check')
                ->action(function () {
                    try {
                        $backupPath = $this->getTranslationService()->createBackup($this->locale);

                        Notification::make()
                            ->title($this->trans('notifications.backup_created.title'))
                            ->body($this->trans('notifications.backup_created.body', [
                                'path' => $backupPath,
                            ]))
                            ->success()
                            ->send();
                    } catch (Exception $e) {
                        Notification::make()
                            ->title($this->trans('notifications.error_creating_backup.title'))
                            ->body($this->trans('notifications.error_creating_backup.body', [
                                'message' => $e->getMessage(),
                            ]))
                            ->danger()
                            ->send();
                    }
                })
                ->color('warning');
        }

        // Statistics action
        if (Config::get('filament-smart-translate.translation_page.features.statistics', true)) {
            $actions[] = Action::make('statistics')
                ->label($this->trans('actions.statistics'))
                ->icon('heroicon-o-chart-bar')
                ->schema([
                    \Filament\Infolists\Components\TextEntry::make('total')
                        ->label($this->trans('modals.statistics.total_translations'))
                        ->state($this->statistics['total'] ?? 0),
                    \Filament\Infolists\Components\TextEntry::make('empty')
                        ->label($this->trans('modals.statistics.empty_translations'))
                        ->state($this->statistics['empty'] ?? 0),
                    \Filament\Infolists\Components\TextEntry::make('long')
                        ->label($this->trans('modals.statistics.long_translations'))
                        ->state($this->statistics['long'] ?? 0),
                    \Filament\Infolists\Components\TextEntry::make('average_length')
                        ->label($this->trans('modals.statistics.average_length'))
                        ->state($this->statistics['average_length'] ?? 0),
                ])
                ->modalHeading($this->trans('modals.statistics.heading'))
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close')
                ->color('info');
        }

        return $actions;
    }
}
