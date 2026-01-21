<?php

namespace Theograms\EditPageTester\Examples;

use Theograms\EditPageTester\Contracts\CustomFieldHandler;
use Theograms\EditPageTester\Iteration;

/**
 * Example implementation showing how to handle custom field types.
 *
 * This example demonstrates handling a hypothetical BelongsToInput field.
 * Copy this class and adapt it for your own custom field types.
 */
class ExampleCustomFieldHandler implements CustomFieldHandler
{

    /**
     * Fill the field in the browser.
     * Return false to skip default handling.
     */
    public function fill(Iteration $i): ?bool
    {
        // Example implementation for a BelongsToInput field:
        /*
        $related = $i->newValue; // The related model to fill

        if ($related) {
            $i->page->click($i->s->dropdownButton());

            if ($i->field->isSearchable()) {
                $titleAttribute = $i->field->getRelationshipTitleAttribute() ?? 'name';
                $i->page->type($i->s->dropdownSearch(), (string)$related->{$titleAttribute});
            }

            $i->page->click($i->s->dropdownOption($related->getKey()));
        } else {
            // Clear the field if value is null
            $i->page->click($i->s->dropdownClearButton());
        }

        return false; // Skip default handling
        */

        return null; // Continue with default handling
    }

    /**
     * Preview/view the field value in the browser.
     * Return false to skip default handling.
     */
    public function preview(Iteration $i): ?bool
    {
        // Example implementation for a BelongsToInput field:
        /*
        if ($i->currentValue) {
            $titleAttribute = $i->field->getRelationshipTitleAttribute() ?? 'name';
            $i->page->assertSeeIn($i->s->dropdownLabel(), (string)$i->currentValue->{$titleAttribute});
        } else {
            $i->page->assertVisible($i->s->dropdownPlaceholder());
        }

        return false; // Skip default handling
        */

        return null; // Continue with default handling
    }

    /**
     * Compare the field values after save.
     * Return false to skip default handling.
     */
    public function compare(Iteration $i): ?bool
    {
        // Example implementation for a BelongsToInput field:
        /*
        $message = "Values are different after save for '{$i->name}' (BelongsToInput)";
        expect($i->currentValue?->getKey())->toBe($i->newValue?->getKey(), $message);

        return false; // Skip default handling
        */

        return null; // Continue with default handling
    }
}