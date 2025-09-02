<?php

namespace Rodrigofs\FilamentAutoTranslate;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Field;
use Filament\Infolists\Components\TextEntry;
use Filament\Navigation\NavigationItem;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Tables\Columns\Column;
use Illuminate\Support\ServiceProvider;

use function Filament\Support\get_model_label;

class TranslationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/filament-auto-translation.php',
            'filament-auto-translation'
        );

        // PhpParser dependencies removed - no longer needed for simplified commands
    }

    public function boot(): void
    {
        // Publish configuration
        $this->publishes([
            __DIR__ . '/../config/filament-auto-translation.php' => config_path('filament-auto-translation.php'),
        ], 'filament-auto-translation-config');

        // Register Artisan commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\StatusCommand::class,
            ]);
        }

        Field::configureUsing(function (Field $field) {
            $field->translateLabel();
        });

        TextEntry::configureUsing(function (TextEntry $entry) {
            $entry->translateLabel();
        });

        Column::configureUsing(function (Column $column) {
            $column->translateLabel();
        });

        Component::configureUsing(function (Component $component) {
            if ($component instanceof Section) {
                $component->translateLabel();

                if ($component->getHeading()) {
                    $component->heading(TranslationHelper::translateWithFallback(
                        $component->getHeading(), 'section_heading')
                    );
                }

                if ($component->getDescription()) {
                    $component->description(TranslationHelper::translateWithFallback(
                        $component->getDescription(), 'section_description')
                    );
                }
            }

            if ($component instanceof Tabs) {
                $component->translateLabel();
            }

            if ($component instanceof Tab) {
                $component->translateLabel();
            }
        });

        CreateAction::configureUsing(function (Action $action) {
            $action->translateLabel();

            if (filled($action->getLabel())) {
                $action->label(fn () => __('filament-actions::create.single.label', ['label' => TranslationHelper::translateWithFallback(get_model_label($action->getModel()), 'actions')]));
            }

            if (filled($action->getModalHeading())) {
                $action->modalHeading(fn () => __('filament-actions::create.single.modal.heading', ['label' => TranslationHelper::translateWithFallback(get_model_label($action->getModel()), 'actions')]));
            }
        }, null, true);

        EditAction::configureUsing(function (EditAction $action) {
            $action->translateLabel();

            if (filled($action->getLabel())) {
                $action->label(fn () => __('filament-actions::edit.single.label', ['label' => TranslationHelper::translateWithFallback(get_model_label($action->getModel()), 'actions')]));
            }

            if (filled($action->getModalHeading())) {
                $action->modalHeading(fn () => __('filament-actions::edit.single.modal.heading', ['label' => TranslationHelper::translateWithFallback(get_model_label($action->getModel()), 'actions')]));
            }
        }, null, true);

        DeleteAction::configureUsing(function (DeleteAction $action) {
            $action->translateLabel();

            if (filled($action->getModalHeading())) {
                $action->modalHeading(fn () => __('filament-actions::delete.single.modal.heading', ['label' => TranslationHelper::translateWithFallback(get_model_label($action->getModel()), 'actions')]));
            }
        }, null, true);

        NavigationItem::configureUsing(function (NavigationItem $navigationGroup) {
            if ($navigationGroup->getGroup()) {
                $navigationGroup->group(TranslationHelper::translateWithFallback(
                    $navigationGroup->getGroup(), 'navigation')
                );
            }
        });
    }
}
