<?php

declare(strict_types=1);

namespace Rodrigofs\FilamentSmartTranslate;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Field;
use Filament\Infolists\Components\Entry;
use Filament\Infolists\Components\TextEntry;
use Filament\Navigation\NavigationItem;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Tables\Columns\Column;
use Illuminate\Support\ServiceProvider;
use Rodrigofs\FilamentSmartTranslate\Support\Overrides\ColumnWrapper;
use Rodrigofs\FilamentSmartTranslate\Support\Overrides\EntryWrapper;
use Rodrigofs\FilamentSmartTranslate\Support\Overrides\FieldWrapper;

use function Filament\Support\get_model_label;

final class TranslationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/filament-smart-translate.php',
            'filament-smart-translate'
        );
    }

    public function boot(): void
    {
        $this->publishConfiguration();
        $this->registerCommands();
        $this->configureFilamentComponents();
    }

    private function publishConfiguration(): void
    {
        $this->publishes([
            __DIR__ . '/../config/filament-smart-translate.php' => config_path('filament-smart-translate.php'),
        ], 'filament-smart-translate-config');
    }

    private function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Console\StatusCommand::class,
            ]);
        }
    }

    private function configureFilamentComponents(): void
    {
        $this->configureLabeledComponents();
        $this->configureSchemaComponents();
        $this->configureActions();
        $this->configureNavigation();
    }

    private function configureLabeledComponents(): void
    {
        // Field components
        Field::configureUsing(function (Field $field): void {
            $field->translateLabel();
            $field->label(fn () => $this->createFieldWrapper($field)->getLabel());
        });

        // TextEntry components
        TextEntry::configureUsing(function (TextEntry $entry): void {
            $entry->translateLabel();
            $entry->label(fn () => $this->createEntryWrapper($entry)->getLabel());
        });

        // Column components
        Column::configureUsing(function (Column $column): void {
            $column->translateLabel();
            $column->label(fn () => $this->createColumnWrapper($column)->getLabel());
        });
    }

    private function createFieldWrapper(Field $component): FieldWrapper
    {
        return new FieldWrapper($component->getName());
    }

    private function createColumnWrapper(Column $component): ColumnWrapper
    {
        return new ColumnWrapper($component->getName());
    }

    private function createEntryWrapper(Entry $component): EntryWrapper
    {
        return new EntryWrapper($component->getName());
    }

    private function configureSchemaComponents(): void
    {
        Component::configureUsing(function (Component $component): void {
            match (true) {
                $component instanceof Section => $this->configureSectionComponent($component),
                $component instanceof Tabs => $this->configureTabsComponent($component),
                $component instanceof Tab => $this->configureTabComponent($component),
                default => null,
            };
        });
    }

    private function configureActions(): void
    {
        // Base Action configuration
        Action::configureUsing(function (Action $action): void {
            $this->configureActionProperties($action, [
                'label' => 'getLabel',
                'modalHeading' => 'getModalHeading',
                'modalDescription' => 'getModalDescription',
            ]);
        });

        // Specific action types with model context
        CreateAction::configureUsing(function (CreateAction $action): void {
            $this->configureModelAction($action, [
                'label' => 'filament-actions::create.single.label',
                'modalHeading' => 'filament-actions::create.single.modal.heading',
            ]);
        }, null, true);

        EditAction::configureUsing(function (EditAction $action): void {
            $this->configureModelAction($action, [
                'label' => 'filament-actions::edit.single.label',
                'modalHeading' => 'filament-actions::edit.single.modal.heading',
            ]);
        }, null, true);

        DeleteAction::configureUsing(function (DeleteAction $action): void {
            $this->configureModelAction($action, [
                'modalHeading' => 'filament-actions::delete.single.modal.heading',
            ]);
        }, null, true);
    }

    private function configureNavigation(): void
    {
        NavigationItem::configureUsing(function (NavigationItem $item): void {
            if ($group = $item->getGroup()) {
                $item->group(TranslationHelper::translateWithFallback($group, 'navigations'));
            }
        });
    }

    private function configureSectionComponent(Section $section): void
    {
        if ($heading = $section->getHeading()) {
            $section->heading(TranslationHelper::translateWithFallback($heading, 'schemas'));
        }

        if ($description = $section->getDescription()) {
            $section->description(TranslationHelper::translateWithFallback($description, 'schemas'));
        }
    }

    private function configureTabsComponent(Tabs $tabs): void
    {
        if ($label = $tabs->getLabel()) {
            $tabs->label(TranslationHelper::translateWithFallback($label, 'schemas'));
        }
    }

    private function configureTabComponent(Tab $tab): void
    {
        if ($label = $tab->getLabel()) {
            $tab->label(TranslationHelper::translateWithFallback($label, 'schemas'));
        }
    }

    /**
     * @param  array<string, string>  $properties
     */
    private function configureActionProperties(Action $action, array $properties): void
    {
        foreach ($properties as $property => $getter) {
            if (! method_exists($action, $getter)) {
                continue;
            }

            $value = $action->{$getter}();
            if (filled($value)) {
                $action->{$property}(TranslationHelper::translateWithFallback($value, 'actions'));
            }
        }
    }

    /**
     * @param  array<string, string>  $translationKeys
     */
    private function configureModelAction(Action $action, array $translationKeys): void
    {
        $model = $action->getModel();
        if (! $model) {
            return;
        }

        $modelLabel = TranslationHelper::translateWithFallback(get_model_label($model), 'actions');

        foreach ($translationKeys as $property => $translationKey) {
            $getter = 'get' . ucfirst($property);
            if (! method_exists($action, $getter) || ! filled($action->{$getter}())) {
                continue;
            }

            $action->{$property}(fn () => __($translationKey, ['label' => $modelLabel]));
        }
    }
}
