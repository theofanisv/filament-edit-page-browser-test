# Filament Edit Page Tester

[![Latest Version](https://img.shields.io/packagist/v/theofanisv/filament-edit-page-browser-test.svg)](https://packagist.org/packages/theofanisv/filament-edit-page-browser-test)
[![Total Downloads](https://img.shields.io/packagist/dt/theofanisv/filament-edit-page-browser-test.svg)](https://packagist.org/packages/theofanisv/filament-edit-page-browser-test)
[![License](https://img.shields.io/packagist/l/theofanisv/filament-edit-page-browser-test.svg)](https://packagist.org/packages/theofanisv/filament-edit-page-browser-test)

A powerful automated browser testing framework for **Filament v4** Edit Pages using **Pest PHP**. This package simplifies end-to-end testing of Filament resource edit pages by providing an elegant API to fill forms, preview field values, and compare saved data.

## Features

- ✅ **Automated Form Filling**: Automatically fill all form fields with test data
- ✅ **Field Preview Testing**: Verify that fields display the correct values
- ✅ **Save Validation**: Ensure form submissions correctly persist data to the database
- ✅ **Supports All Standard Filament Fields**: TextInput, Select, DatePicker, Toggle, Checkbox, RichEditor, CodeEditor, KeyValue, and more
- ✅ **Custom Field Support**: Extensible architecture for your custom field types
- ✅ **Fluent API**: Chain methods for readable and maintainable tests
- ✅ **Verbose Logging**: Optional detailed logging for debugging test failures
- ✅ **Display Value Mapping**: Handle fields where the stored value differs from the display value

## Requirements

- PHP 8.2 or 8.3
- Laravel 11+
- Filament v4
- Pest v3 or v4 with Browser Testing plugin

## Installation

Install the package via Composer:

```bash
composer require theofanisv/filament-edit-page-browser-test --dev
```

## Quick Start

Here's a simple example testing a User edit page:

```php
use App\Models\User;
use Theograms\EditPageTester\EditPageTester;

it('can preview and save user data', function () {
    visit([]); // Required to run in *Test.php https://github.com/pestphp/pest/issues/1439

    $current = User::factory()->create();
    $new = User::factory()->make();

    EditPageTester::make($current, $new)
        ->testPreview()    // Test that current values display correctly
        ->testSave();      // Fill form with new values and save

    // expect($current->fresh()->name)->toBe($new->name); // Auto checked during testSave()
});
```

## Basic Usage

### 1. Testing Field Preview

Test that the edit page correctly displays existing field values:

```php
use Theograms\EditPageTester\EditPageTester;

it('displays current values correctly', function () {
    visit([]); // Required to run in *Test.php https://github.com/pestphp/pest/issues/1439
    
    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'is_active' => true,
    ]);

    EditPageTester::make($user)
        ->testPreview();
});
```

### 2. Testing Form Submission

Test that the form correctly saves new values:

```php
it('saves new values correctly', function () {
    visit([]); // Required to run in *Test.php https://github.com/pestphp/pest/issues/1439
    
    $current = User::factory()->create();
    $new = User::factory()->make([
        'name' => 'Jane Smith',
        'email' => 'jane@example.com',
    ]);

    EditPageTester::make($current, $new)
        ->testSave();

    //expect($current->fresh()) // Auto checked during testSave()
    //    ->name->toBe('Jane Smith')
    //    ->email->toBe('jane@example.com');
});
```

### 3. Combined Testing

Test both preview and save in a single test:

```php
it('can preview and update user', function () {
    visit([]); // Required to run in *Test.php https://github.com/pestphp/pest/issues/1439
    
    $current = User::factory()->create();
    $new = User::factory()->make();

    EditPageTester::make($current, $new)
        ->testPreview()    // First verify current values display
        ->testSave();      // Then test updating to new values
});
```

## Advanced Usage

### Verbose Logging

Enable verbose logging to see detailed information about each field being tested:

```php
EditPageTester::make($current, $new)
    ->verbose()
    ->testSave();
```

Output:
```
* EditUser testing fill: name (TextInput)
* EditUser testing fill: email (TextInput)
* EditUser testing fill: is_active (Toggle)
* EditUser testing saved: name (TextInput)
* EditUser testing saved: email (TextInput)
* EditUser testing saved: is_active (Toggle)
```

### Display Value Mapping

For fields where the stored value differs from the display value (e.g., Select fields):

```php
$statusMap = [
    'active' => 'Active',
    'inactive' => 'Inactive',
    'pending' => 'Pending',
];

EditPageTester::make($user)
    ->withValuesDisplayMap('status', $statusMap)
    ->testPreview();
```

### Custom Field Callbacks

Skip or customize handling for specific fields:

#### Preview Callback

```php
EditPageTester::make($user)
    ->previewFieldUsing(function (string $name, Field $field, AwaitableWebpage $page) {
        if ($name === 'avatar_url') {
            // Custom preview logic
            return false; // Skip default handling
        }
    })
    ->testPreview();
```

#### Fill Callback

```php
EditPageTester::make($current, $new)
    ->fillFieldUsing(function (string $name, Field $field, AwaitableWebpage $page) {
        if ($name === 'password') {
            // Custom fill logic
            return false; // Skip default handling
        }
    })
    ->testSave();
```

#### Compare Callback

```php
EditPageTester::make($current, $new)
    ->compareValueUsing(function (string $name, Field $field, Model $current, Model $new) {
        if ($name === 'last_login_at') {
            // Skip comparison for this field
            return false;
        }
    })
    ->testSave();
```

### Required Field Validation

Ensure specific fields are visible on the edit page:

```php
EditPageTester::make($user)
    ->requiredVisibleFields(['name', 'email', 'role'])
    ->testPreview();
```

## Supported Field Types

The package supports all standard Filament v4 field types out of the box:

| Field Type | Fill Support | Preview Support | Compare Support |
|------------|--------------|-----------------|-----------------|
| TextInput | ✅ | ✅ | ✅ |
| Textarea | ✅ | ✅ | ✅ |
| Select | ✅ | ✅ | ✅ |
| RichEditor | ✅ | ✅ | ✅ |
| DatePicker | ✅ | ✅ | ✅ |
| DateTimePicker | ✅ | ✅ | ✅ |
| TimePicker | ❌ | ❌ | ✅ |
| Toggle | ✅ | ✅ | ✅ |
| Checkbox | ✅ | ✅ | ✅ |
| CheckboxList | ✅ | ✅ | ✅ |
| CodeEditor | ✅ | ✅ | ✅ |
| KeyValue | ✅ | ✅ | ✅ |

## Custom Field Types

You can extend the tester to support your own custom field types by implementing the `CustomFieldHandler` interface:

### Step 1: Create a Custom Field Handler

```php
<?php

namespace App\Tests\Support;

use Theograms\EditPageTester\Contracts\CustomFieldHandler;
use Theograms\EditPageTester\Iteration;

class BelongsToInputHandler implements CustomFieldHandler
{
    public function fill(Iteration $i): ?bool
    {
        // Only handle BelongsToInput fields
        if (!$i->field instanceof \App\Filament\Forms\BelongsToInput) {
            return null; // Continue with default handling
        }

        $related = $i->newValue;

        if ($related) {
            $i->page->click($i->s->dropdownButton());

            if ($i->field->isSearchable()) {
                $titleAttribute = $i->field->getRelationshipTitleAttribute() ?? 'name';
                $i->page->type($i->s->dropdownSearch(), (string)$related->{$titleAttribute});
            }

            $i->page->click($i->s->dropdownOption($related->getKey()));
        } else {
            $i->page->click($i->s->dropdownClearButton());
        }

        return false; // Skip default handling
    }

    public function preview(Iteration $i): ?bool
    {
        if (!$i->field instanceof \App\Filament\Forms\BelongsToInput) {
            return null;
        }

        if ($i->currentValue) {
            $titleAttribute = $i->field->getRelationshipTitleAttribute() ?? 'name';
            $i->page->assertSeeIn($i->s->dropdownLabel(), (string)$i->currentValue->{$titleAttribute});
        } else {
            $i->page->assertVisible($i->s->dropdownPlaceholder());
        }

        return false; // Skip default handling
    }

    public function compare(Iteration $i): ?bool
    {
        if (!$i->field instanceof \App\Filament\Forms\BelongsToInput) {
            return null;
        }

        $message = "Values are different after save for '{$i->name}'";
        expect($i->currentValue?->getKey())->toBe($i->newValue?->getKey(), $message);

        return false; // Skip default handling
    }
}
```

### Iteration Properties

The `Iteration` object provides access to all context needed for handling a field:

| Property | Type | Description |
|----------|------|-------------|
| `$i->name` | `string` | The field name/key |
| `$i->field` | `Field` | The Filament field instance |
| `$i->page` | `AwaitableWebpage\|null` | The browser page (null for compare) |
| `$i->s` | `FilamentSelector` | CSS selector helper for this field |
| `$i->currentModel` | `Model` | The current Eloquent model |
| `$i->newModel` | `Model\|null` | The new model with updated values |
| `$i->currentValue` | `mixed` | Shortcut for `$i->currentModel->{$i->name}` |
| `$i->newValue` | `mixed` | Shortcut for `$i->newModel->{$i->name}` |

### Return Values

Each handler method is called for every field and returns `?bool`:
- `null` - Field not handled, continue with default handling
- `false` - Field handled, skip default handling

### Step 2: Use the Custom Handler

```php
use App\Tests\Support\BelongsToInputHandler;

EditPageTester::make($current, $new)
    ->withCustomFieldHandler(new BelongsToInputHandler())
    ->testSave();
```

## CSS Selector Reference

The `FilamentSelector` class provides helper methods for common Filament field selectors:

```php
$s = new FilamentSelector('field_name');

// Dropdown/Select fields
$s->dropdownButton();           // Button to open dropdown
$s->dropdownLabel();            // Selected value label
$s->dropdownPlaceholder();      // Placeholder text
$s->dropdownSearch();           // Search input in dropdown
$s->dropdownOption('value');    // Specific option in dropdown
$s->dropdownClearButton();      // Clear selection button

// Date/DateTime pickers
$s->datetimeTrigger();          // Button to open picker
$s->datetimeYearInput();        // Year input
$s->datetimeMonthSelect();      // Month select
$s->datetimeDayDiv(15);         // Day cell (15th day)
$s->datetimeHourInput();        // Hour input
$s->datetimeMinuteInput();      // Minute input
$s->datetimeSecondInput();      // Second input

// KeyValue field
$s->keyValueTable();            // Entire table
$s->keyValueKeyInput(1);        // Key input for row 1
$s->keyValueValueInput(1);      // Value input for row 1
$s->keyValueDeleteRowButton(1); // Delete button for row 1
$s->keyValueAddRowButton();     // Add new row button

// CheckboxList
$s->checkboxListItem('value');  // Specific checkbox
$s->checkboxListItems();        // All checkboxes

// Other fields
$s->input();                    // TextInput/Textarea
$s->richText();                 // RichEditor content area
$s->toggleButton();             // Toggle button
$s->checkbox();                 // Checkbox input
$s->codeEditor();               // CodeEditor content area
$s->sectionHeader();            // Section header
```

## Date/DateTime Formatting

Override the default date formatting methods in your test:

```php
use Theograms\EditPageTester\EditPageTester;

class CustomEditPageTester extends EditPageTester
{
    protected function formatDateTime($datetime): string
    {
        return $datetime?->format('m/d/Y g:i A') ?? '';
    }

    protected function formatDate($date): string
    {
        return $date?->format('m/d/Y') ?? '';
    }
}

// Use your custom tester
CustomEditPageTester::make($user)->testPreview();
```

## Testing Relationships

### BelongsTo Relationships

```php
$post = Post::factory()->create();
$author = User::factory()->create();
$newAuthor = User::factory()->create();

// Set up the relationship
$post->author()->associate($author)->save();

// Test changing the relationship
EditPageTester::make($post)
    ->fillFieldUsing(function ($name, $field, $page) use ($newAuthor) {
        if ($name === 'author_id') {
            $s = new FilamentSelector($name);
            $page->click($s->dropdownButton())
                ->type($s->dropdownSearch(), $newAuthor->name)
                ->click($s->dropdownOption($newAuthor->id));
            return false; // Skip default handling
        }
    })
    ->testSave();

expect($post->fresh()->author_id)->toBe($newAuthor->id);
```

### BelongsToMany Relationships (CheckboxList)

```php
$user = User::factory()->create();
$roles = Role::factory()->count(3)->create();

// Attach some initial roles
$user->roles()->attach($roles->take(2));

// Test changing roles
$newRoles = Role::factory()->count(2)->create();

EditPageTester::make($user)
    ->fillFieldUsing(function ($name, $field, $page) use ($newRoles) {
        if ($name === 'roles') {
            // The CheckboxList will be automatically handled
            // Just ensure your new model has the relationship loaded
        }
    })
    ->testSave();
```

## Best Practices

1. **Use Factories**: Always use model factories to create test data for consistency.

2. **Test One Thing**: Keep tests focused on testing a single edit page or scenario.

3. **Use Verbose Mode During Development**: Enable verbose logging when writing tests to see what's happening.

4. **Handle Custom Fields Properly**: Create custom field handlers for your application-specific field types.

5. **Test Both Preview and Save**: Ensure both display and persistence work correctly.

6. **Use Fresh Instances**: Always refresh the model after save to get the latest data from the database.

## Troubleshooting

### Field Not Found

If you get an error about a field not being testable:

```
RuntimeException: Component Filament\Forms\Components\CustomField (field_name) is not testable for filling
```

**Solution**: Create a custom field handler for that field type or use callbacks to handle it manually.

### Values Not Matching After Save

If values don't match after save:

1. Enable verbose logging to see which field is failing
2. Check if the field needs special formatting (dates, rich text, etc.)
3. Use a custom compare callback for that field
4. Verify the model's mutators and casts

### Display Value Mismatch

If a field shows a different value than what's stored (common with Select fields):

**Solution**: Use `withValuesDisplayMap()` to map stored values to display values.

## Examples

### Complete User Edit Page Test

```php
use App\Models\User;
use App\Models\Role;
use Theograms\EditPageTester\EditPageTester;

it('can fully test user edit page', function () {
    visit([]); // Required to run in *Test.php https://github.com/pestphp/pest/issues/1439
    
    // Arrange
    $role = Role::factory()->create(['name' => 'Admin']);
    $currentUser = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'is_active' => true,
        'role_id' => $role->id,
    ]);

    $newRole = Role::factory()->create(['name' => 'Editor']);
    $newUser = User::factory()->make([
        'name' => 'Jane Smith',
        'email' => 'jane@example.com',
        'is_active' => false,
        'role_id' => $newRole->id,
    ]);
    $newUser->setRelation('role', $newRole);

    // Act & Assert
    EditPageTester::make($currentUser, $newUser)
        ->verbose()
        ->requiredVisibleFields(['name', 'email', 'is_active', 'role_id'])
        ->withValuesDisplayMap('role_id', [
            $role->id => $role->name,
            $newRole->id => $newRole->name,
        ])
        ->testPreview()
        ->testSave();

    // Additional assertions
    expect($currentUser->fresh())
        ->name->toBe('Jane Smith')
        ->email->toBe('jane@example.com')
        ->is_active->toBe(false)
        ->role_id->toBe($newRole->id);
});
```

### Testing with Custom Field Handler

```php
use App\Tests\Support\BelongsToInputHandler;

it('can test edit page with custom fields', function () {
    visit([]); // Required to run in *Test.php https://github.com/pestphp/pest/issues/1439
    
    $handler = new BelongsToInputHandler();

    $product = Product::factory()->create();
    $newProduct = Product::factory()->make();

    EditPageTester::make($product, $newProduct)
        ->withCustomFieldHandler($handler)
        ->testSave();
});
```

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.

## Credits

- "Why do something by hand when you can automate it?" 
