# Filament Smart Translation

[![Tests](https://github.com/rodrigofs/filament-smart-translate/actions/workflows/run-tests.yml/badge.svg)](https://github.com/rodrigofs/filament-smart-translate/actions/workflows/run-tests.yml)
[![PHPStan](https://github.com/rodrigofs/filament-smart-translate/actions/workflows/phpstan.yml/badge.svg)](https://github.com/rodrigofs/filament-smart-translate/actions/workflows/phpstan.yml)
[![Code Style](https://github.com/rodrigofs/filament-smart-translate/actions/workflows/fix-code-style.yml/badge.svg)](https://github.com/rodrigofs/filament-smart-translate/actions/workflows/fix-code-style.yml)
[![Coverage](https://github.com/rodrigofs/filament-smart-translate/actions/workflows/test-coverage.yml/badge.svg)](https://github.com/rodrigofs/filament-smart-translate/actions/workflows/test-coverage.yml)

![License](https://img.shields.io/badge/License-MIT-green.svg?style=for-the-badge)
![Filament v4](https://img.shields.io/badge/Filament-v4.0+-FF6B35?style=for-the-badge&logo=laravel)
![Laravel](https://img.shields.io/badge/Laravel-v12+-FF2D20?style=for-the-badge&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.4+-777BB4?style=for-the-badge&logo=php)

**A comprehensive Laravel package designed exclusively for Filament v4 applications** that provides automatic translation support for all Filament components. **Form fields, table columns, actions, and layout components** work automatically with zero configuration. **Resources, Pages, and Clusters** require simple trait implementation for full translation support.

## ✨ Features

- **🎯 Filament v4 Native**: Built specifically for Filament v4 architecture and components
- **⚡ Zero Configuration**: Form fields, columns, actions work instantly with zero configuration
- **🔧 Trait-Based Architecture**: Resources, Pages, Clusters require simple trait addition
- **🎛️ Smart Fallback System**: Advanced fallback strategies with extensible architecture
- **⚡ Performance Optimized**: Efficient translation with minimal overhead and strategy instance reuse
- **🌐 Multi-locale Support**: Full support for Laravel's multi-language features
- **📊 Status Command**: Visual overview of implementation status and missing translations
- **🔄 Service Provider Integration**: Leverages Filament v4's component configuration system
- **🧪 Fully Tested**: 152+ tests with 76.3% coverage ensuring reliability
- **🚀 CI/CD Ready**: Automated testing, code style, PHPStan, and release workflows

## 📦 Installation

### 1. Install via Composer

```bash
composer require rodrigofs/filament-smart-translate
```

### 2. Publish Configuration (Optional)

```bash
php artisan vendor:publish --tag=filament-smart-translate-config
```

### 3. Check Package Status (Optional)

```bash
php artisan filament-smart-translate:status
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
use Rodrigofs\FilamentSmartTranslate\Resource\ResourceTranslateble;
use Rodrigofs\FilamentSmartTranslate\Page\PageTranslateble;
use Rodrigofs\FilamentSmartTranslate\Cluster\ClusterTranslateble;

class UserResource extends Resource
{
    use ResourceTranslateble; // Required for model labels
}

// Pages  
class Settings extends Page
{
    use PageTranslateble; // Required for navigation groups
}

// Clusters
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
use Rodrigofs\FilamentSmartTranslate\Resource\ResourceTranslateble;

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
use Rodrigofs\FilamentSmartTranslate\Page\PageTranslateble;

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
use Rodrigofs\FilamentSmartTranslate\Cluster\ClusterTranslateble;

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
    'enabled' => env('FILAMENT_SMART_TRANSLATE_ENABLED', true),
    
    // Component-specific settings
    'components' => [
        'resource_labels' => [
            'enabled' => true,
            'fallback_strategy' => 'original' // humanize, original, title_case
        ],
        'navigations' => [
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
        ],
        'navigation_groups' => [
            'enabled' => true,
            'fallback_strategy' => 'original'
        ]
    ],
    
    // Custom fallback strategies
    'fallback_strategies' => [
        // 'custom_strategy' => \App\Strategies\CustomFallbackStrategy::class,
    ],
    
    // Debug settings
    'debug' => [
        'log_missing_translations' => env('FILAMENT_SMART_TRANSLATE_DEBUG', false),
        'log_fallback_usage' => env('FILAMENT_SMART_TRANSLATE_DEBUG', false),
    ],
];
```

## 🎛️ Fallback Strategies System

When a translation is missing, the package applies intelligent fallback strategies to provide a better user experience. The system supports three built-in strategies and allows custom implementations.

### Built-in Fallback Strategies

#### 1. `original` Strategy (Default)
Keeps the original key unchanged:

```php
'fallback_strategy' => 'original'
```

**Examples:**
- `user_name` → `user_name`
- `email_address` → `email_address`
- `navigation_group` → `navigation_group`

**Best for:** When you prefer to see the exact key names for debugging or when keys are already in a readable format.

#### 2. `humanize` Strategy  
Converts keys to human-readable format:

```php
'fallback_strategy' => 'humanize'
```

**Examples:**
- `user_name` → `User_Name`
- `emailAddress` → `Email Address`
- `first_name_field` → `First_Name_Field`
- `userProfileData` → `User Profile Data`

**Best for:** Development environments or when you want automatic readable labels without creating translations.

#### 3. `title_case` Strategy
Applies title case formatting:

```php
'fallback_strategy' => 'title_case'
```

**Examples:**
- `user name` → `User Name`  
- `email address` → `Email Address`
- `profile data` → `Profile Data`
- `user-name field` → `User-name Field`

**Best for:** When keys are already separated by spaces and you want proper capitalization.

### Component-Specific Fallback Configuration

You can configure different fallback strategies for different component types:

```php
'components' => [
    'resource_labels' => [
        'enabled' => true,
        'fallback_strategy' => 'humanize' // User-friendly for resource names
    ],
    'navigations' => [
        'enabled' => true,
        'fallback_strategy' => 'title_case' // Clean navigation labels
    ],
    'actions' => [
        'enabled' => true,
        'fallback_strategy' => 'original' // Keep action names as-is
    ],
    'clusters' => [
        'enabled' => true,
        'fallback_strategy' => 'humanize' // Readable cluster names
    ],
    'pages' => [
        'enabled' => true,
        'fallback_strategy' => 'title_case' // Professional page names
    ]
]
```

### Custom Fallback Strategies

You can create custom fallback strategies by implementing the `FallbackStrategyInterface`:

#### 1. Create a Custom Strategy Class

```php
<?php

namespace App\Strategies;

use Rodrigofs\FilamentSmartTranslate\Support\Fallback\FallbackStrategyInterface;

class UppercaseStrategy implements FallbackStrategyInterface
{
    public function apply(string $key): string
    {
        return strtoupper(str_replace('_', ' ', $key));
    }
}
```

#### 2. Register the Strategy

```php
// config/filament-smart-translate.php
'fallback_strategies' => [
    'uppercase' => \App\Strategies\UppercaseStrategy::class,
],

'components' => [
    'actions' => [
        'fallback_strategy' => 'uppercase' // Use your custom strategy
    ]
]
```

#### 3. Using Closures for Simple Strategies

For simple transformations, you can use closures directly in the configuration:

```php
'fallback_strategies' => [
    'prefix_strategy' => function ($key) {
        return '🔸 ' . ucfirst($key);
    },
    'snake_to_kebab' => function ($key) {
        return str_replace('_', '-', $key);
    }
],
```

### Fallback Strategy Architecture

The fallback system uses a sophisticated architecture with these components:

- **`FallbackStrategyInterface`**: Contract that all strategies must implement
- **`FallbackStrategyManager`**: Resolves and reuses strategy instances during request lifecycle
- **Built-in Strategies**: `HumanizeStrategy`, `OriginalStrategy`, `TitleCaseStrategy`
- **Custom Strategy Support**: Full support for user-defined strategies

#### Strategy Resolution Flow

1. **Configuration Check**: Component-specific fallback strategy is loaded
2. **Strategy Resolution**: Manager resolves strategy (class or closure)
3. **Instance Reuse**: Strategy instances are reused within the same request for performance
4. **Fallback Chain**: If strategy fails, falls back to `humanize` strategy
5. **Error Handling**: Graceful degradation to prevent application crashes

#### Advanced Strategy Example

```php
<?php

namespace App\Strategies;

use Rodrigofs\FilamentSmartTranslate\Support\Fallback\FallbackStrategyInterface;

class LocalizedPrefixStrategy implements FallbackStrategyInterface
{
    public function apply(string $key): string
    {
        $locale = app()->getLocale();
        $formatted = ucwords(str_replace(['_', '-'], ' ', $key));
        
        return match($locale) {
            'pt_BR' => "🇧🇷 {$formatted}",
            'es' => "🇪🇸 {$formatted}",
            'fr' => "🇫🇷 {$formatted}",
            default => $formatted
        };
    }
}
```

### Environment Variables

Control fallback behavior via environment variables:

```env
# Enable/disable translation system
FILAMENT_SMART_TRANSLATE_ENABLED=true

# Enable debug logging for fallback usage
FILAMENT_SMART_TRANSLATE_DEBUG=false
```

### Debug Fallback Usage

Enable logging to see which fallback strategies are being used:

```php
'debug' => [
    'log_missing_translations' => true,
    'log_fallback_usage' => true,
]
```

This will log entries like:
```
[2024-12-19 10:30:15] local.INFO: Filament Smart Translation: Missing translation
{
    "key": "user_profile",
    "component": "resource_labels", 
    "fallback_strategy": "humanize",
    "locale": "pt_BR"
}
```

## 🌍 Translation Structure

The package supports multiple translation key patterns with intelligent fallback:

### Component-Prefixed Keys (Recommended)
```json
// lang/en.json
{
    "resource_labels.user": "User",
    "navigation_groups.admin": "Administration",
    "actions.create": "Create",
    "cluster.user_management": "User Management"
}
```

### Direct Keys (Fallback)
```json
// lang/en.json  
{
    "name": "Name",
    "email": "Email",
    "password": "Password",
    "user": "User"
}
```

### Translation Resolution Order

The package tries to find translations in this order:

1. **Component-prefixed key**: `resource_labels.user`
2. **Direct key**: `user`  
3. **Fallback strategy**: Applied based on component configuration

This intelligent resolution ensures maximum flexibility while maintaining clean translation files.

### Nested Keys (Alternative PHP files)
```php
// lang/en/navigation.php
return [
    'user_management' => 'User Management',
    'settings' => 'Settings',
];

// lang/en/resource_labels.php  
return [
    'user' => 'User',
    'post' => 'Post',
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
use Rodrigofs\FilamentSmartTranslate\Resource\ResourceTranslateble;

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
                Forms\Components\Section::make('Profile')
                    ->schema([
                        Forms\Components\TextInput::make('first_name'),
                        Forms\Components\TextInput::make('last_name'),
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
    "first_name": "Primeiro Nome",
    "last_name": "Último Nome",
    "profile": "Perfil",
    "resource_labels.user": "Usuário", 
    "navigation_groups.user_management": "Gerenciamento de Usuários",
    "actions.create": "Criar",
    "actions.edit": "Editar", 
    "actions.delete": "Excluir"
}
```

**Result:** Complete Portuguese interface with automatic fallbacks for missing keys!

## 📊 Package Status Command

Use the status command to get a visual overview of your package configuration:

```bash
php artisan filament-smart-translate:status
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
  ║  Filament Smart Translation - Status Report              ║
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

  💡 Tip: For better control, consider adding traits to candidates:
     • ResourceTranslateble - For resources with custom model labels
     • PageTranslateble - For pages with navigation groups
     • ClusterTranslateble - For clusters with custom breadcrumbs
```

## 🧪 Testing

The package includes a comprehensive test suite with 152+ tests:

```bash
# Run all tests
composer test

# Run tests with coverage
composer test-coverage

# Run code formatting
composer pint

# Run static analysis
composer phpstan

# Run complete quality check
composer quality
```

**Test Coverage Highlights:**
- **76.3%** overall coverage
- **100%** coverage on all fallback strategies
- **100%** coverage on core translation logic
- Comprehensive integration tests with Filament components
- Edge case handling and error scenarios

## 🔧 Troubleshooting

### Translations Not Showing?

1. **Check your locale**: Ensure `config/app.php` has the correct locale
2. **Verify translation files**: Make sure your translation keys exist
3. **Clear config cache**: Run `php artisan config:clear`
4. **Check configuration**: Ensure the package is enabled in configuration
5. **Add missing traits**: Resources, Pages, and Clusters require traits to work
6. **Use status command**: Run `php artisan filament-smart-translate:status` to see what's configured

### Resources, Pages, or Clusters Not Translating?

This is expected behavior. These components **require traits** to enable translation:

```php
// Add to your Resource
use Rodrigofs\FilamentSmartTranslate\Resource\ResourceTranslateble;

// Add to your Page  
use Rodrigofs\FilamentSmartTranslate\Page\PageTranslateble;

// Add to your Cluster
use Rodrigofs\FilamentSmartTranslate\Cluster\ClusterTranslateble;
```

Run `php artisan filament-smart-translate:status` to see which files need traits.

### Debug Missing Translations

Enable debug logging in your configuration:

```php
'debug' => [
    'log_missing_translations' => true,
    'log_fallback_usage' => true,
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

### Custom Fallback Strategy Not Working?

Ensure your custom strategy is properly configured:

1. **Class exists**: Verify the class implements `FallbackStrategyInterface`
2. **Correct namespace**: Check the namespace in your configuration
3. **Config cache clear**: Run `php artisan config:clear` after configuration changes
4. **Debug logging**: Enable debug to see fallback usage

```php
// Verify your strategy implements the interface
class CustomStrategy implements FallbackStrategyInterface
{
    public function apply(string $key): string
    {
        return $key; // Your logic here
    }
}
```

### Performance Issues?

The package is optimized for performance:

- **Strategy reuse**: Fallback strategy instances are reused within the same request
- **Minimal overhead**: Only processes components that need translation
- **Lazy loading**: Translation only happens when needed

If you experience issues:

1. **Check config**: Ensure Laravel's configuration is loaded properly
2. **Optimize translation files**: Use JSON format for better performance
3. **Profile queries**: Use Laravel Telescope to identify bottlenecks

## 🏗️ Architecture

The package uses a clean, extensible architecture:

### Core Components

- **`TranslationServiceProvider`**: Registers global component configurations
- **`TranslationHelper`**: Handles translation logic with intelligent fallbacks
- **`FallbackStrategyManager`**: Manages and resolves fallback strategies
- **Component Traits**: Optional traits for Resources/Pages/Clusters

### Fallback Strategy System

- **`FallbackStrategyInterface`**: Contract for all fallback strategies
- **Built-in Strategies**: `HumanizeStrategy`, `OriginalStrategy`, `TitleCaseStrategy`
- **Strategy Resolution**: Automatic class and closure resolution
- **Performance Optimization**: Strategy instances reused for optimal performance

### Global Component Configuration

The package leverages Filament's `Component::configureUsing()` method to automatically apply translations to all components without requiring code changes.

```php
// Simplified example of how the package works internally
Field::configureUsing(function (Field $component): void {
    $component->translateLabel();
});
```

## 📖 Requirements

- **PHP**: 8.4+
- **Laravel**: 12+
- **Filament**: 4.0+

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

### Development Setup

1. Clone the repository
2. Install dependencies: `composer install`
3. Run tests: `composer test`
4. Check code style: `composer pint`
5. Run static analysis: `composer phpstan`

## 📝 License

This project is licensed under the **MIT License** - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgements

- **Filament Team**: For creating an amazing admin panel framework
- **Laravel Team**: For the robust foundation
- **Community Contributors**: For feedback and suggestions

---

**Made with ❤️ for the Filament community**

[![GitHub stars](https://img.shields.io/github/stars/rodrigofs/filament-smart-translate?style=social)](https://github.com/rodrigofs/filament-smart-translate)
