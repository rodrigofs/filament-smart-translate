<?php

declare(strict_types=1);

namespace Rodrigofs\FilamentSmartTranslate;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Field;
use Filament\Infolists\Components\TextEntry;
use Filament\Navigation\NavigationItem;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Tables\Columns\Column;
use Rodrigofs\FilamentSmartTranslate\Support\Overrides\ColumnWrapper;
use Rodrigofs\FilamentSmartTranslate\Support\Overrides\EntryWrapper;
use Rodrigofs\FilamentSmartTranslate\Support\Overrides\FieldWrapper;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

use function Filament\Support\get_model_label;

final class TranslationServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-smart-translate';

    public function configurePackage(Package $package): void
    {
        $package->name('filament-smart-translate')
            ->hasCommands($this->getCommands())
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->askToStarRepoOnGitHub('rodrigofs/filament-smart-translate');
            });

        $configFileName = $package->shortName();

        if (file_exists($package->basePath("/../config/{$configFileName}.php"))) {
            $package->hasConfigFile();
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews('filament-smart-translate');
        }

        if (file_exists($package->basePath('/../resources/lang'))) {
            $package->hasTranslations();
        }
    }

    public function packageRegistered(): void
    {
        // Register translation service
        $this->app->singleton(Services\TranslationService::class, function ($app) {
            return new Services\TranslationService(
                base_path('lang'),
                $app['config']->get('filament-smart-translate.available_locales', ['pt_BR', 'en', 'es', 'fr'])
            );
        });
    }

    public function packageBooted(): void
    {
        $this->configureFilamentComponents();
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            Console\StatusCommand::class,
        ];
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
            $field->label(fn () => (new FieldWrapper($field->getName()))->getLabel());
        });

        // TextEntry components
        TextEntry::configureUsing(function (TextEntry $entry): void {
            $entry->translateLabel();
            $entry->label(fn () => (new EntryWrapper($entry->getName()))->getLabel());
        });

        // Column components
        Column::configureUsing(function (Column $column): void {
            $column->translateLabel();
            $column->label(fn () => (new ColumnWrapper($column->getName()))->getLabel());
        });
    }

    private function configureSchemaComponents(): void
    {
        $provider = $this;
        Component::configureUsing(function (Component $component) use ($provider): void {
            match (true) {
                $component instanceof Section => $provider->configureSectionComponent($component),
                $component instanceof Fieldset => $provider->configureFieldsetComponent($component),
                $component instanceof Tabs => $provider->configureTabsComponent($component),
                $component instanceof Tab => $provider->configureTabComponent($component),
                default => null,
            };
        });
    }

    private function configureActions(): void
    {
        $provider = $this;
        // Base Action configuration
        Action::configureUsing(function (Action $action) use ($provider): void {
            $provider->configureActionProperties($action, [
                'label' => 'getLabel',
                'modalHeading' => 'getModalHeading',
                'modalDescription' => 'getModalDescription',
            ]);
        });

        // Specific action types with model context
        CreateAction::configureUsing(function (CreateAction $action) use ($provider): void {
            $provider->configureModelAction($action, [
                'label' => 'filament-actions::create.single.label',
                'modalHeading' => 'filament-actions::create.single.modal.heading',
            ]);
        }, null, true);

        EditAction::configureUsing(function (EditAction $action) use ($provider): void {
            $provider->configureModelAction($action, [
                'label' => 'filament-actions::edit.single.label',
                'modalHeading' => 'filament-actions::edit.single.modal.heading',
            ]);
        }, null, true);

        DeleteAction::configureUsing(function (DeleteAction $action) use ($provider): void {
            $provider->configureModelAction($action, [
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
        $section->translateLabel();

        if ($heading = $section->getHeading()) {
            $section->heading(TranslationHelper::translateWithFallback($heading, 'schemas'));
        }

        if ($description = $section->getDescription()) {
            $section->description(TranslationHelper::translateWithFallback($description, 'schemas'));
        }
    }

    private function configureFieldsetComponent(Fieldset $section): void
    {
        $section->translateLabel();

        if ($label = $section->getLabel()) {
            $section->label(TranslationHelper::translateWithFallback($label, 'schemas'));
        }
    }

    private function configureTabsComponent(Tabs $tabs): void
    {
        $tabs->translateLabel();
        if ($label = $tabs->getLabel()) {
            $tabs->label(TranslationHelper::translateWithFallback($label, 'schemas'));
        }
    }

    private function configureTabComponent(Tab $tab): void
    {
        $tab->translateLabel();
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
