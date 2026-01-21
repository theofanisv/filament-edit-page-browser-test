<?php

namespace Theograms\EditPageTester\Contracts;

use Theograms\Forms\Components\Field;
use Pest\Browser\Api\AwaitableWebpage;

interface CustomFieldHandler
{
    /**
     * Determine if this handler can handle the given field.
     */
    public function canHandle(Field $field): bool;

    /**
     * Fill the field in the browser.
     */
    public function fill(string $name, Field $field, AwaitableWebpage $page, mixed $value): void;

    /**
     * Preview/view the field value in the browser.
     */
    public function preview(string $name, Field $field, AwaitableWebpage $page, mixed $value): void;

    /**
     * Compare the field values after save.
     */
    public function compare(string $name, Field $field, mixed $currentValue, mixed $newValue): void;
}
