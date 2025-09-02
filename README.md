# Filament Auto Translation

**A comprehensive Laravel package designed exclusively for Filament v4 applications** that provides automatic translation support for all Filament components. **Form fields, table columns, actions, and layout components** work automatically with zero configuration. **Resources, Pages, and Clusters** require simple trait implementation for full translation support.

![Filament v4](https://img.shields.io/badge/Filament-v4.0+-FF6B35?style=for-the-badge&logo=laravel)
![Laravel](https://img.shields.io/badge/Laravel-v10+-FF2D20?style=for-the-badge&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php)

## ✨ Features

- **🎯 Filament v4 Native**: Built specifically for Filament v4 architecture and components
- **⚡ Zero Configuration**: Form fields, columns, actions work instantly with zero configuration
- **🔧 Trait-Based Architecture**: Resources, Pages, Clusters require simple trait addition
- **🎛️ Smart Fallbacks**: Configurable fallback strategies when translations are missing
- **⚡ Performance Optimized**: Efficient translation with minimal overhead
- **🌐 Multi-locale Support**: Full support for Laravel's multi-language features
- **📊 Status Command**: Visual overview of implementation status and missing translations
- **🔄 Service Provider Integration**: Leverages Filament v4's component configuration system

## 📦 Installation

### 1. Install via Composer

```bash
composer require rodrigofs/filament-auto-translation
```

### 2. Publish Configuration (Optional)

```bash
php artisan vendor:publish --tag=filament-auto-translation-config
```

### 3. Check Package Status (Optional)

```bash
php artisan filament-auto-translation:status
```

This command shows a visual overview of your package configuration, trait usage, and component coverage.

The package is now ready to use! Laravel's auto-discovery will automatically register the service provider.

## 🚀 Quick Start

### 1. Create Translation Files

The package uses Laravel's standard translation system. Create JSON files for your languages:

**JSON format (`lang/pt_BR.json`, `lang/es.json`, etc.)**
```json
{
    "name": "Nome",
    "email": "E-mail",
    "user": "Usuário",
    "users": "Usuários",
    "admin": "Administração",
    "settings": "Configurações",
    "create": "Criar",
    "edit": "Editar",
    "delete": "Excluir"
}
```

**Nested format (optional - using PHP files)**
```php
// lang/pt_BR/navigation.php (alternative approach)
<?php

return [
    'dashboard' => 'Painel',
    'user_management' => 'Gerenciamento de Usuários',
    'settings' => 'Configurações',
];
```

### 2. Set Your Locale

Configure your application locale in `config/app.php`:

```php
'locale' => 'pt_BR', // or any supported locale
```

### 3. Add Traits to Resources, Pages & Clusters (Required)

For Resources, Pages, and Clusters to have translation, you must add the appropriate traits:

```php
// Resources
use Rodrigofs\FilamentAutoTranslate\Resource\Concerns\ResourceTranslateble;

class UserResource extends Resource
{
    use ResourceTranslateble; // Required for model labels
}

// Pages  
use Rodrigofs\FilamentAutoTranslate\Page\PageTranslateble;

class Settings extends Page
{
    use PageTranslateble; // Required for navigation groups
}

// Clusters
use Rodrigofs\FilamentAutoTranslate\Cluster\ClusterTranslateble;

class UserManagement extends Cluster
{
    use ClusterTranslateble; // Required for navigation/breadcrumbs
}
```

Your Filament interface will now display translated labels automatically for components and with traits for Resources, Pages & Clusters!

## 🎯 How It Works

The package provides **two levels of translation**:

### ✅ Automatic Translation (No Code Changes Required)

These components are automatically configured to use `translateLabel()`:

- **Form Fields**: `TextInput`, `Select`, `Checkbox`, etc.
- **Table Columns**: `TextColumn`, `BooleanColumn`, etc.
- **Actions**: `CreateAction`, `EditAction`, `DeleteAction`, etc.
- **Layout Components**: `Section`, `Tabs`, `Tab`

### 🔧 Trait-Based Translation (Manual Implementation Required)

These components **require traits** to enable translation:

- **Resources**: Model labels and navigation groups → Use `ResourceTranslateble` trait
- **Pages**: Navigation groups → Use `PageTranslateble` trait  
- **Clusters**: Navigation and breadcrumbs → Use `ClusterTranslateble` trait

> **Important**: Without traits, Resources, Pages, and Clusters will **not** have automatic translation. You must add the appropriate trait to each class to enable translation for these components.

## 🔧 Translation Traits (Required for Resources, Pages & Clusters)

To enable translation for Resources, Pages, and Clusters, you **must** add the appropriate traits:

### Resource Trait

**Required** for Resources to enable model label and navigation group translation:

```php
<?php

namespace App\Filament\Resources;

use Filament\Resources\Resource;
use Rodrigofs\FilamentAutoTranslate\Resource\Concerns\ResourceTranslateble;

class UserResource extends Resource
{
    use ResourceTranslateble;
    
    protected static ?string $model = User::class;
    protected static ?string $navigationGroup = 'user_management';
    
    // The trait will automatically translate:
    // - getModelLabel() using 'resource_labels' prefix
    // - getNavigationGroup() using 'navigation_groups' prefix
}
```

**Translation files needed:**
```json
// lang/pt_BR.json
{
    "resource_labels.user": "Usuário",
    "navigation_groups.user_management": "Gerenciamento de Usuários"
}
```

### Page Trait

**Required** for Pages to enable navigation group translation:

```php
<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Rodrigofs\FilamentAutoTranslate\Page\PageTranslateble;

class Settings extends Page
{
    use PageTranslateble;
    
    protected static ?string $navigationGroup = 'administration';
    
    // The trait will automatically translate navigation groups
}
```

### Cluster Trait

**Required** for Clusters to enable navigation and breadcrumb translation:

```php
<?php

namespace App\Filament\Clusters;

use Filament\Clusters\Cluster;
use Rodrigofs\FilamentAutoTranslate\Cluster\ClusterTranslateble;

class UserManagement extends Cluster
{
    use ClusterTranslateble;
    
    // The trait will automatically translate:
    // - getClusterBreadcrumb() using 'cluster' prefix
}
```

**Translation files needed:**
```json
// lang/pt_BR.json
{
    "cluster.user_management": "Gerenciamento de Usuários"
}
```

### Summary: When Traits Are Required

| Component Type | Automatic Translation | Trait Required | What Gets Translated |
|---|---|---|---|
| **Form Fields** | ✅ Yes | ❌ No | Field labels |
| **Table Columns** | ✅ Yes | ❌ No | Column headers |
| **Actions** | ✅ Yes | ❌ No | Action labels |
| **Layout Components** | ✅ Yes | ❌ No | Section/Tab labels |
| **Resources** | ❌ No | ✅ Yes | Model labels, navigation groups |
| **Pages** | ❌ No | ✅ Yes | Navigation groups |
| **Clusters** | ❌ No | ✅ Yes | Navigation, breadcrumbs |

> **Key Point**: Form fields, table columns, actions, and layout components work automatically. Resources, Pages, and Clusters require manual trait implementation.

## 🛠️ Configuration

The package works without configuration, but you can customize its behavior:

```php
<?php

return [
    // Enable/disable the entire translation system
    'enabled' => env('FILAMENT_AUTO_TRANSLATION_ENABLED', true),
    
    // Component-specific settings
    'components' => [
        'resource_labels' => [
            'enabled' => true,
            'fallback_strategy' => 'original' // humanize, original, title_case
        ],
        'navigation' => [
            'enabled' => true,
            'fallback_strategy' => 'original'
        ],
        'actions' => [
            'enabled' => true,
            'fallback_strategy' => 'original'
        ],
        'clusters' => [
            'enabled' => true,
            'fallback_strategy' => 'original'
        ],
        'pages' => [
            'enabled' => true,
            'fallback_strategy' => 'original'
        ]
    ],
    
    // Fallback strategies for missing translations
    'fallback_strategies' => [
        'humanize' => fn ($key) => Str::title(Str::snake($key, ' ')),
        'original' => fn ($key) => $key,
        'title_case' => fn ($key) => Str::ucwords($key),
        'custom' => null, // Define your own closure
    ],
    
    // Debug settings
    'debug' => [
        'log_missing_translations' => env('FILAMENT_AUTO_TRANSLATION_DEBUG', false),
        'log_fallback_usage' => env('FILAMENT_AUTO_TRANSLATION_DEBUG', false),
    ],
];
```

### Environment Variables

You can control the package behavior via environment variables:

```env
# Enable/disable translation system
FILAMENT_AUTO_TRANSLATION_ENABLED=true

# Enable debug logging
FILAMENT_AUTO_TRANSLATION_DEBUG=false
```

### Fallback Strategies

When a translation is missing, the system applies fallback strategies:

- **`original`**: Keep the original text as-is (`user_name` → `user_name`)
- **`humanize`**: Convert to readable format (`user_name` → `User Name`)
- **`title_case`**: Apply title case (`user profile` → `User Profile`)
- **`custom`**: Define your own transformation logic

## 🌍 Translation Structure

The package supports multiple translation key patterns:

### Component-Prefixed Keys
```json
// lang/en.json
{
    "resource_labels.user": "User",
    "navigation.dashboard": "Dashboard",
    "actions.create": "Create"
}
```

### Direct Keys
```json
// lang/en.json
{
    "name": "Name",
    "email": "Email",
    "password": "Password"
}
```

### Nested Keys (Alternative PHP files)
```php
// lang/en/navigation.php (alternative approach)
return [
    'user_management' => 'User Management',
    'settings' => 'Settings',
];
```

## 💡 Examples

### Before (without translation)
```php
class UserResource extends Resource 
{
    protected static ?string $model = User::class;
    protected static ?string $navigationGroup = 'Admin';
    
    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('name'),
            TextInput::make('email'),
        ]);
    }
}
```

### After (with Portuguese translations)
With `pt_BR` locale and proper translations, the same resource automatically shows:
- "Nome" instead of "name"
- "E-mail" instead of "email" 
- "Administração" instead of "Admin"

### Complete Example with Traits

```php
<?php

namespace App\Filament\Resources;

use App\Models\User;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Rodrigofs\FilamentAutoTranslate\Resource\Concerns\ResourceTranslateble;

class UserResource extends Resource
{
    use ResourceTranslateble; // 🎯 Add the trait for enhanced translation
    
    protected static ?string $model = User::class;
    
    // These will be automatically translated:
    protected static ?string $navigationGroup = 'user_management';
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Labels automatically translated via service provider
                Forms\Components\TextInput::make('name')
                    ->required(),
                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required(),
                Forms\Components\Select::make('role')
                    ->options([
                        'admin' => 'Administrator',
                        'user' => 'User',
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Column headers automatically translated
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('email'),
                Tables\Columns\TextColumn::make('role'),
            ])
            ->actions([
                // Action labels automatically translated
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
```

**Required translation files:**
```json
// lang/pt_BR.json
{
    "name": "Nome",
    "email": "E-mail",
    "role": "Função",
    "admin": "Administrador", 
    "user": "Usuário",
    "resource_labels.user": "Usuário",
    "navigation_groups.user_management": "Gerenciamento de Usuários",
    "actions.create": "Criar",
    "actions.edit": "Editar",
    "actions.delete": "Excluir"
}
```

**Result:** Complete Portuguese interface with zero code changes needed!

## 🧪 Testing

Run the test suite:

```bash
# Run all tests
composer test

# Run tests with coverage
composer test-coverage

# Run code formatting
composer pint
```

## 📊 Package Status Command

Use the status command to get a visual overview of your package configuration:

```bash
php artisan filament-auto-translation:status
```

**What it shows:**
- ✅ **Package Status**: Whether the package is enabled or disabled
- 🎯 **Trait Usage**: Which traits are being used and where (no duplicates)
- ⚠️ **Trait Candidates**: Files that could use traits but don't (Resources, Pages, Clusters)
- 🔧 **Component Coverage**: Status of each component type with fallback strategies
- 📊 **Coverage Summary**: Overall percentage, trait implementation status, and helpful tips

**Example output:**
```
  ╔══════════════════════════════════════════════════════════╗
  ║  Filament Auto Translation - Status Report              ║
  ╚══════════════════════════════════════════════════════════╝

  📦 Package Status: ✓ ENABLED

  🎯 Trait Usage:
    ✓ ResourceTranslateble (2 files)
      └─ app/Filament/Resources/UserResource.php
      └─ app/Filament/Resources/PostResource.php

    ⚠ Files that could use traits:
    ○ PageTranslateble (1 candidate)
      └─ app/Filament/Pages/Settings.php
    ○ ClusterTranslateble (1 candidate)
      └─ app/Filament/Clusters/AdminCluster.php

  🔧 Component Coverage:
    ✓ Resource Labels (original)
    ✓ Navigation (humanize)
    ✓ Actions (title_case)
    ✓ Clusters (original)
    ✓ Pages (original)

  📊 Coverage Summary:
    ▓ Active components: 5/5 (100%)
    ▓ Implemented traits: 2 files
    ▓ Candidates without traits: 2 files (could use traits)
```

## 🔧 Troubleshooting

### Translations Not Showing?

1. **Check your locale**: Ensure `config/app.php` has the correct locale
2. **Verify translation files**: Make sure your translation keys exist
3. **Clear cache**: Run `php artisan cache:clear`
4. **Check configuration**: Ensure the package is enabled in configuration
5. **Add missing traits**: Resources, Pages, and Clusters require traits to work
6. **Use status command**: Run `php artisan filament-auto-translation:status` to see what's configured

### Resources, Pages, or Clusters Not Translating?

This is expected behavior. These components **require traits** to enable translation:

```php
// Add to your Resource
use Rodrigofs\FilamentAutoTranslate\Resource\Concerns\ResourceTranslateble;

// Add to your Page  
use Rodrigofs\FilamentAutoTranslate\Page\PageTranslateble;

// Add to your Cluster
use Rodrigofs\FilamentAutoTranslate\Cluster\ClusterTranslateble;
```

Run `php artisan filament-auto-translation:status` to see which files need traits.

### Debug Missing Translations

Enable debug logging in your configuration:

```php
'debug' => [
    'log_missing_translations' => true,
],
```

This will log missing translations to help you identify what keys need translation.

### Disable for Specific Components

You can disable translation for specific component types:

```php
'components' => [
    'actions' => [
        'enabled' => false, // Disable action translation
    ],
],
```

### Custom Fallback Strategy

Define your own fallback logic:

```php
'fallback_strategies' => [
    'custom' => function ($key) {
        // Your custom transformation logic
        return strtoupper($key);
    },
],

'components' => [
    'resource_labels' => [
        'fallback_strategy' => 'custom',
    ],
],
```

## 🏗️ Architecture

The package uses Laravel's service provider to globally configure Filament components:

- **TranslationServiceProvider**: Registers global component configurations
- **TranslationHelper**: Handles translation logic with fallbacks
- **Component Traits**: Optional traits for custom resources/pages/clusters

### Global Component Configuration

The package leverages Filament's `Component::configureUsing()` method to automatically apply translations to all components without requiring code changes.

## 📖 Requirements

- PHP 8.2+
- Laravel 10+
- Filament 4.0+

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📝 License

Proprietary - Rodrigo Fernandes

---

**Made with ❤️ for the Filament community**
