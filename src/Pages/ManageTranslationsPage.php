<?php

declare(strict_types=1);

namespace Rodrigofs\FilamentSmartTranslate\Pages;

use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
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

    private ?TranslationService $translationService = null;

    public function mount(): void
    {
        $this->locale = app()->getLocale();
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
        $translations = $this->getTranslationService()->loadTranslations($this->locale);

        $this->translations = $translations->map(function ($value, $key) {
            $data = new TranslationData($key, $value, $this->locale);

            return [
                'key' => $data->key,
                'value' => $data->value,
                'locale' => $data->locale,
                'category' => $data->getCategory(),
                'length' => $data->getLength(),
                'is_empty' => ! $data->isEmpty(),
                'is_long' => $data->isLong(),
            ];
        });
    }

    protected function loadStatistics(): void
    {
        $this->statistics = $this->getTranslationService()->getStatistics($this->locale);
    }

    public function table(Table $table): Table
    {
        return $table
            ->records(function (int $page, int $recordsPerPage): LengthAwarePaginator {

                $skip = ($page - 1) * $recordsPerPage;

                return new LengthAwarePaginator(
                    items: $this->translations->slice($skip, $recordsPerPage),
                    total: $this->translations->count(),
                    perPage: $recordsPerPage,
                    currentPage: $page
                );
            })
            ->columns([
                Tables\Columns\TextColumn::make('key')
                    ->label('Chave de Tradução')
                    ->sortable()
                    ->searchable()
                    ->copyable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('value')
                    ->label('Valor da Tradução')
                    ->sortable()
                    ->searchable()
                    ->limit(100)
                    ->wrap(),

                Tables\Columns\TextColumn::make('category')
                    ->label('Categoria')
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
                    ->label('Tamanho')
                    ->sortable()
                    ->alignCenter()
                    ->color(fn(int $state): string => $state > 100 ? 'warning' : 'gray'),

                Tables\Columns\IconColumn::make('is_empty')
                    ->label('Vazio')
                    ->boolean()
                    ->alignCenter(),
            ])
            ->headerActions([
                Action::make('add_translation')
                    ->label('Nova Tradução')
                    ->icon('heroicon-o-plus')
                    ->color('primary')
                    ->schema([
                        Select::make('locale')
                            ->label('Idioma')
                            ->options(array_combine(
                                $this->getTranslationService()->getAvailableLocales(),
                                $this->getTranslationService()->getAvailableLocales()
                            ))
                            ->default($this->locale)
                            ->required(),

                        TextInput::make('key')
                            ->label('Chave')
                            ->required()
                            ->rules([
                                'string',
                                'max:255',
                                function ($attribute, $value, $fail) {
                                    $translations = $this->getTranslationService()->loadTranslations($this->locale);
                                    if ($translations->has($value)) {
                                        $fail('Esta chave já existe.');
                                    }
                                },
                            ])
                            ->placeholder('ex: resources.user'),

                        Textarea::make('value')
                            ->label('Tradução')
                            ->required()
                            ->maxLength(1000)
                            ->rows(3),
                    ])
                    ->action(fn(array $data) => $this->addTranslation($data))
                    ->successNotificationTitle('Tradução adicionada com sucesso!'),
            ])
            ->recordActions([
                EditAction::make()
                    ->label('Editar')
                    ->icon('heroicon-o-pencil')
                    ->form([
                        TextInput::make('key')
                            ->label('Chave')
                            ->required()
                            ->disabled()
                            ->dehydrated(false),

                        Textarea::make('value')
                            ->label('Tradução')
                            ->required()
                            ->maxLength(1000)
                            ->rows(3),
                    ])
                    ->fillForm(fn(array $record): array => [
                        'key' => $record['key'],
                        'value' => $record['value'],
                    ])
                    ->action(fn(array $data, array $record) => $this->updateTranslation($record['key'], $data['value']))
                    ->successNotificationTitle('Tradução atualizada com sucesso!'),


            ])
            ->toolbarActions([
                \Filament\Actions\BulkAction::make('delete')
                    ->label('Excluir Selecionadas')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Excluir Traduções Selecionadas')
                    ->modalDescription('Tem certeza de que deseja excluir as traduções selecionadas? Esta ação não pode ser desfeita.')
                    ->action(fn(Collection $records) => $this->bulkDeleteTranslations($records->pluck('key')->toArray()))
                    ->successNotificationTitle('Traduções excluídas com sucesso!'),
            ])
            ->emptyStateIcon('heroicon-o-language')
            ->emptyStateHeading('Nenhuma tradução encontrada')
            ->emptyStateDescription('Comece adicionando uma nova tradução usando o botão acima.')
            ->striped()
            ->defaultSort('key');
    }


    public function addTranslation(array $data): void
    {
        try {
            $locale = $data['locale'] ?? $this->locale;

            $this->getTranslationService()->addTranslation($locale, $data['key'], $data['value']);

            // Update current locale if needed
            if ($locale === $this->locale) {
                $this->loadTranslations();
                $this->loadStatistics();
            }

            Notification::make()
                ->title('Tradução adicionada')
                ->body("A tradução '{$data['key']}' foi adicionada com sucesso.")
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Erro ao adicionar tradução')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function updateTranslation(string $key, string $value): void
    {
        try {
            $this->getTranslationService()->updateTranslation($this->locale, $key, $value);

            $this->loadTranslations();
            $this->loadStatistics();

            Notification::make()
                ->title('Tradução atualizada')
                ->body("A tradução '{$key}' foi atualizada com sucesso.")
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Erro ao atualizar tradução')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function deleteTranslation(string $key): void
    {
        try {
            $this->getTranslationService()->deleteTranslation($this->locale, $key);

            $this->loadTranslations();
            $this->loadStatistics();

            Notification::make()
                ->title('Tradução excluída')
                ->body("A tradução '{$key}' foi excluída com sucesso.")
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Erro ao excluir tradução')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function bulkDeleteTranslations(array $keys): void
    {
        try {
            $result = $this->getTranslationService()->bulkDeleteTranslations($this->locale, $keys);

            $this->loadTranslations();
            $this->loadStatistics();

            $successful = count($keys) - count($result);
            $failed = count($result);

            Notification::make()
                ->title('Traduções excluídas')
                ->body("Excluídas {$successful} de " . count($keys) . " traduções selecionadas.")
                ->success()
                ->send();

            if ($failed > 0) {
                Notification::make()
                    ->title('Algumas traduções não foram excluídas')
                    ->body("Falha ao excluir {$failed} traduções.")
                    ->warning()
                    ->send();
            }

        } catch (\Exception $e) {
            Notification::make()
                ->title('Erro ao excluir traduções')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    protected function getActions(): array
    {
        return [
            Action::make('change_locale')
                ->label('Alterar Idioma')
                ->icon('heroicon-o-globe-alt')
                ->form([
                    Select::make('locale')
                        ->label('Selecionar Idioma')
                        ->options(array_combine(
                            $this->getTranslationService()->getAvailableLocales(),
                            $this->getTranslationService()->getAvailableLocales()
                        ))
                        ->default($this->locale)
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->locale = $data['locale'];
                    $this->loadTranslations();
                    $this->loadStatistics();

                    Notification::make()
                        ->title('Idioma alterado')
                        ->body("Exibindo traduções para: {$this->locale}")
                        ->success()
                        ->send();
                }),

            Action::make('refresh')
                ->label('Recarregar')
                ->icon('heroicon-o-arrow-path')
                ->action(function () {
                    $this->loadTranslations();
                    $this->loadStatistics();

                    Notification::make()
                        ->title('Traduções recarregadas')
                        ->success()
                        ->send();
                }),

            Action::make('export')
                ->label('Exportar')
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
                ->color('gray'),

            Action::make('backup')
                ->label('Criar Backup')
                ->icon('heroicon-o-shield-check')
                ->action(function () {
                    try {
                        $backupPath = $this->getTranslationService()->createBackup($this->locale);

                        Notification::make()
                            ->title('Backup criado')
                            ->body("Backup salvo em: {$backupPath}")
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Erro ao criar backup')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->color('warning'),

            Action::make('statistics')
                ->label('Estatísticas')
                ->icon('heroicon-o-chart-bar')
                ->schema([
                    \Filament\Infolists\Components\TextEntry::make('total')
                        ->label('Total de Traduções')
                        ->state($this->statistics['total'] ?? 0),
                    \Filament\Infolists\Components\TextEntry::make('empty')
                        ->label('Traduções Vazias')
                        ->state($this->statistics['empty'] ?? 0),
                    \Filament\Infolists\Components\TextEntry::make('long')
                        ->label('Traduções Longas (>100 chars)')
                        ->state($this->statistics['long'] ?? 0),
                    \Filament\Infolists\Components\TextEntry::make('average_length')
                        ->label('Tamanho Médio')
                        ->state($this->statistics['average_length'] ?? 0),
                ])
                ->modalHeading('Estatísticas das Traduções')
                ->color('info'),
        ];
    }
}
