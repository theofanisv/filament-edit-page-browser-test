<?php

/**
 * Example Test File
 *
 * This is an example showing how to use the Filament Edit Page Tester
 * in your Pest tests. Copy this to your tests/Feature directory and adapt
 * it to your needs.
 */

use App\Models\User;
use App\Models\Role;
use Theograms\EditPageTester\EditPageTester;

/**
 * Basic Example: Test Preview
 *
 * Test that the edit page correctly displays the current model values.
 */
it('displays user data correctly on edit page', function () {
    visit([]); // Required to run in *Test.php https://github.com/pestphp/pest/issues/1439

    $user = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'is_active' => true,
    ]);

    EditPageTester::make($user)
        ->testPreview();
});

/**
 * Basic Example: Test Save
 *
 * Test that the edit page correctly saves new values.
 */
it('can update user data', function () {
    visit([]); // Required to run in *Test.php https://github.com/pestphp/pest/issues/1439

    $current = User::factory()->create();
    $new = User::factory()->make([
        'name' => 'Jane Smith',
        'email' => 'jane@example.com',
        'is_active' => false,
    ]);

    EditPageTester::make($current, $new)
        ->testSave();

    //expect($current->fresh()) // Auto checked during testSave()
    //    ->name->toBe('Jane Smith')
    //    ->email->toBe('jane@example.com')
    //    ->is_active->toBe(false);
});

/**
 * Advanced Example: Combined Preview and Save
 *
 * Test both preview and save in a single test with verbose logging.
 */
it('can preview and update user with all fields', function () {
    visit([]); // Required to run in *Test.php https://github.com/pestphp/pest/issues/1439

    $role = Role::factory()->create(['name' => 'Admin']);
    $current = User::factory()->create([
        'name' => 'John Doe',
        'email' => 'john@example.com',
        'is_active' => true,
        'role_id' => $role->id,
    ]);

    $newRole = Role::factory()->create(['name' => 'Editor']);
    $new = User::factory()->make([
        'name' => 'Jane Smith',
        'email' => 'jane@example.com',
        'is_active' => false,
        'role_id' => $newRole->id,
    ]);
    $new->setRelation('role', $newRole);

    EditPageTester::make($current, $new)
        ->verbose()  // Enable verbose logging
        ->requiredVisibleFields(['name', 'email', 'is_active', 'role_id'])
        ->withValuesDisplayMap('role_id', [
            $role->id => $role->name,
            $newRole->id => $newRole->name,
        ])
        ->testPreview()
        ->testSave();

    expect($current->fresh())
        ->name->toBe('Jane Smith')
        ->email->toBe('jane@example.com')
        ->is_active->toBe(false)
        ->role_id->toBe($newRole->id);
});

/**
 * Advanced Example: Custom Field Callbacks
 *
 * Use callbacks to customize handling of specific fields.
 */
it('can update user with custom field handling', function () {
    visit([]); // Required to run in *Test.php https://github.com/pestphp/pest/issues/1439

    $current = User::factory()->create();
    $new = User::factory()->make();

    EditPageTester::make($current, $new)
        ->fillFieldUsing(function (string $name, $field, $page) {
            if ($name === 'avatar_url') {
                // Custom handling for avatar upload
                // ...
                return false; // Skip default handling
            }
        })
        ->compareValueUsing(function (string $name, $field, $current, $new) {
            if ($name === 'last_login_at') {
                // Skip comparison for auto-updated timestamp
                return false;
            }
        })
        ->testSave();
});

/**
 * Advanced Example: Custom Field Handler
 *
 * Use a custom field handler for application-specific field types.
 */
it('can test edit page with custom field handler', function () {
    visit([]); // Required to run in *Test.php https://github.com/pestphp/pest/issues/1439

    // Assuming you have a custom BelongsToInputHandler
    // $handler = new \App\Tests\Support\BelongsToInputHandler();

    $current = User::factory()->create();
    $new = User::factory()->make();

    EditPageTester::make($current, $new)
        // ->withCustomFieldHandler($handler)
        ->testSave();
});

/**
 * Advanced Example: Testing Relationships
 *
 * Test fields that involve relationships.
 */
it('can update user with belongsTo relationship', function () {
    visit([]); // Required to run in *Test.php https://github.com/pestphp/pest/issues/1439

    $oldManager = User::factory()->create(['name' => 'Old Manager']);
    $user = User::factory()->create([
        'manager_id' => $oldManager->id,
    ]);

    $newManager = User::factory()->create(['name' => 'New Manager']);
    $updatedUser = User::factory()->make([
        'manager_id' => $newManager->id,
    ]);
    $updatedUser->setRelation('manager', $newManager);

    EditPageTester::make($user, $updatedUser)
        ->withValuesDisplayMap('manager_id', [
            $oldManager->id => $oldManager->name,
            $newManager->id => $newManager->name,
        ])
        ->testSave();

    expect($user->fresh()->manager_id)->toBe($newManager->id);
});

/**
 * Advanced Example: Testing Date Fields
 *
 * Test date and datetime fields.
 */
it('can update user with date fields', function () {
    visit([]); // Required to run in *Test.php https://github.com/pestphp/pest/issues/1439

    $current = User::factory()->create([
        'birth_date' => now()->subYears(30),
        'hired_at' => now()->subYear(),
    ]);

    $new = User::factory()->make([
        'birth_date' => now()->subYears(25),
        'hired_at' => now()->subMonths(6),
    ]);

    EditPageTester::make($current, $new)
        ->testSave();

    expect($current->fresh())
        ->birth_date->toDateString()->toBe($new->birth_date->toDateString())
        ->hired_at->toDateTimeString()->toBe($new->hired_at->toDateTimeString());
});

/**
 * Advanced Example: Testing Toggle and Checkbox Fields
 *
 * Test boolean fields.
 */
it('can toggle boolean fields', function () {
    visit([]); // Required to run in *Test.php https://github.com/pestphp/pest/issues/1439

    $current = User::factory()->create([
        'is_active' => false,
        'receives_notifications' => false,
        'is_verified' => false,
    ]);

    $new = User::factory()->make([
        'is_active' => true,
        'receives_notifications' => true,
        'is_verified' => true,
    ]);

    EditPageTester::make($current, $new)
        ->testSave();

    expect($current->fresh())
        ->is_active->toBeTrue()
        ->receives_notifications->toBeTrue()
        ->is_verified->toBeTrue();
});

/**
 * Advanced Example: Testing RichEditor and CodeEditor
 *
 * Test complex text fields.
 */
it('can update rich text and code fields', function () {
    visit([]); // Required to run in *Test.php https://github.com/pestphp/pest/issues/1439

    $current = User::factory()->create([
        'bio' => 'Old bio',
        'custom_css' => '.old { color: red; }',
    ]);

    $new = User::factory()->make([
        'bio' => 'New bio with rich text',
        'custom_css' => '.new { color: blue; }',
    ]);

    EditPageTester::make($current, $new)
        ->testSave();

    // Note: RichEditor wraps content in <p> tags
    expect($current->fresh()->bio)->toContain('New bio with rich text');
    expect($current->fresh()->custom_css)->toBe('.new { color: blue; }');
});

/**
 * Advanced Example: Testing KeyValue Fields
 *
 * Test key-value pair fields.
 */
it('can update key-value fields', function () {
    visit([]); // Required to run in *Test.php https://github.com/pestphp/pest/issues/1439

    $current = User::factory()->create([
        'metadata' => json_encode([
            'key1' => 'value1',
            'key2' => 'value2',
        ]),
    ]);

    $new = User::factory()->make([
        'metadata' => json_encode([
            'key3' => 'value3',
            'key4' => 'value4',
            'key5' => 'value5',
        ]),
    ]);

    EditPageTester::make($current, $new)
        ->testSave();

    $metadata = json_decode($current->fresh()->metadata, true);
    expect($metadata)->toHaveKeys(['key3', 'key4', 'key5']);
});

/**
 * Advanced Example: Required Fields Validation
 *
 * Ensure specific fields are visible on the edit page.
 */
it('has all required fields visible', function () {
    visit([]); // Required to run in *Test.php https://github.com/pestphp/pest/issues/1439

    $user = User::factory()->create();

    EditPageTester::make($user)
        ->requiredVisibleFields([
            'name',
            'email',
            'role_id',
            'is_active',
        ])
        ->testPreview();
});
