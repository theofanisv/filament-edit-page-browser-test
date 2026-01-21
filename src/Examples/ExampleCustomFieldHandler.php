<?php

namespace Theograms\EditPageTester\Examples;

use Theograms\EditPageTester\Contracts\CustomFieldHandler;
use Theograms\EditPageTester\FilamentSelector;
use Theograms\Forms\Components\Field;
use Pest\Browser\Api\AwaitableWebpage;

/**
 * Example implementation showing how to handle custom field types.
 *
 * This example demonstrates handling a hypothetical BelongsToInput field.
 * Copy this class and adapt it for your own custom field types.
 */
class ExampleCustomFieldHandler implements CustomFieldHandler
{
    /**
     * Determine if this handler can handle the given field.
     */
    public function canHandle(Field $field): bool
    {
        // Example: Check if the field is a custom type
        // return $field instanceof \App\Filament\Forms\BelongsToInput;

        // For this example, we return false since it's just a template
        return false;
    }

    /**
     * Fill the field in the browser.
     */
    public function fill(string $name, Field $field, AwaitableWebpage $page, mixed $value): void
    {
        $s = new FilamentSelector($name);

        // Example implementation for a BelongsToInput field:
        /*
        $related = $value; // Assuming $value is the related model

        if ($related) {
            $page->click($s->dropdownButton());

            if ($field->isSearchable()) {
                $titleAttribute = $field->getRelationshipTitleAttribute() ?? 'name';
                $page->type($s->dropdownSearch(), (string)$related->{$titleAttribute});
            }

            $page->click($s->dropdownOption($related->getKey()));
        } else {
            // Clear the field if value is null
            $page->click($s->dropdownClearButton());
        }
        */
    }

    /**
     * Preview/view the field value in the browser.
     */
    public function preview(string $name, Field $field, AwaitableWebpage $page, mixed $value): void
    {
        $s = new FilamentSelector($name);

        // Example implementation for a BelongsToInput field:
        /*
        if ($value) {
            $titleAttribute = $field->getRelationshipTitleAttribute() ?? 'name';
            $page->assertSeeIn($s->dropdownLabel(), (string)$value->{$titleAttribute});
        } else {
            $page->assertVisible($s->dropdownPlaceholder());
        }
        */
    }

    /**
     * Compare the field values after save.
     */
    public function compare(string $name, Field $field, mixed $currentValue, mixed $newValue): void
    {
        // Example implementation for a BelongsToInput field:
        /*
        $message = "Values are different after save for '$name' (BelongsToInput)";
        expect($currentValue?->getKey())->toBe($newValue?->getKey(), $message);
        */
    }
}
