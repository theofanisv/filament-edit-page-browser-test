<?php

namespace Theograms\EditPageTester\Contracts;

use Filament\Forms\Components\Field;
use Theograms\EditPageTester\Iteration;

interface CustomFieldHandler
{
    /**
     * Fill the field in the browser.
     * @return bool|null Return false to skip the field.
     */
    public function fill(Iteration $i): ?bool;

    /**
     * Preview/view the field value in the browser.
     * @return bool|null Return false to skip the field.
     */
    public function preview(Iteration $i): ?bool;

    /**
     * Compare the field values after save.
     * @return bool|null Return false to skip the field.
     */
    public function compare(Iteration $i): ?bool;
}
