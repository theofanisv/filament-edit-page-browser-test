# Installation Guide

This package can be installed either from Packagist (when published) or as a local package during development.

## Installing from Packagist (Recommended)

Once published to Packagist, install via Composer:

```bash
composer require theofanisv/filament-edit-page-browser-test --dev
```

## Installing as a Local Package

### Option 1: Using Path Repository (Development)

If you're developing this package alongside your application, you can use Composer's path repository feature.

1. **Place the package** in a `packages/` directory in your Laravel project:
   ```
   your-laravel-project/
   ├── app/
   ├── packages/
   │   └── filament-edit-page-tester/
   ├── tests/
   └── composer.json
   ```

2. **Update your project's composer.json** to add the path repository:

   ```json
   {
       "repositories": [
           {
               "type": "path",
               "url": "./packages/filament-edit-page-tester",
               "options": {
                   "symlink": true
               }
           }
       ],
       "require-dev": {
           "theofanisv/filament-edit-page-browser-test": "@dev"
       }
   }
   ```

3. **Install the package**:

   ```bash
   composer update theofanisv/filament-edit-page-browser-test
   ```

   The package will be symlinked from `packages/filament-edit-page-tester` to `vendor/theofanisv/filament-edit-page-browser-test`.

### Option 2: Using VCS Repository (GitHub/GitLab)

If the package is hosted in a private repository:

1. **Update your project's composer.json**:

   ```json
   {
       "repositories": [
           {
               "type": "vcs",
               "url": "https://github.com/yourusername/filament-edit-page-tester.git"
           }
       ],
       "require-dev": {
           "theofanisv/filament-edit-page-browser-test": "dev-main"
       }
   }
   ```

2. **Install the package**:

   ```bash
   composer update theofanisv/filament-edit-page-browser-test
   ```

## Verifying Installation

After installation, verify the package is available:

```bash
composer show theofanisv/filament-edit-page-browser-test
```

You should see package information including version and dependencies.

## Usage After Installation

1. **Create a test** using the package:

   ```php
   <?php

   use App\Models\User;
   use Theograms\EditPageTester\EditPageTester;

   it('can test user edit page', function () {
       $user = User::factory()->create();
       $new = User::factory()->make();

       EditPageTester::make($user, $new)
           ->testSave();
   });
   ```

2. **Run your test**:

   ```bash
   php artisan test --filter="can test user edit page"
   ```

## Troubleshooting

### Class not found

If you get a "Class not found" error:

1. Clear Composer autoload cache:
   ```bash
   composer dump-autoload
   ```

2. Verify the package is in `vendor/`:
   ```bash
   ls -la vendor/theofanisv/filament-edit-page-browser-test
   ```

### Pest browser functions not working

Ensure you have Pest browser testing plugin installed:

```bash
composer require pestphp/pest-plugin-laravel --dev
```

### Filament classes not found

Ensure Filament v4 is installed:

```bash
composer require filament/filament:"^4.0"
```

## Next Steps

- Read the [README.md](README.md) for full documentation
- Check [examples/UserEditPageTest.php](examples/UserEditPageTest.php) for usage examples
- Review [CONTRIBUTING.md](CONTRIBUTING.md) if you want to contribute
