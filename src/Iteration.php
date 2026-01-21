<?php

namespace Theograms\EditPageTester;

use Filament\Forms\Components\Field;
use Filament\Schemas\Components\Component;
use Illuminate\Database\Eloquent\Model;
use Pest\Browser\Api\AwaitableWebpage;
use Pest\Browser\Api\Webpage;

class Iteration
{
    /**
     * HTML Selector for this field name.
     */
    public readonly FilamentSelector $s;
    public readonly mixed $currentValue;
    public readonly mixed $newValue;

    /**
     * @param Field|Component $field Component class is used instead of Field for special fields like MorphToSelect or custom fields.
     * @param AwaitableWebpage|null $page Is always available during preview and form fill, never when comparing values.
     * @param Model|null $newModel Is always available during form fill and comparing values, never on preview.
     */
    public function __construct(
        public readonly string            $name,
        public readonly Field|Component   $field,
        public readonly Model             $currentModel,
        public readonly EditPageTester    $edit_page_tester,
        public readonly ?AwaitableWebpage $page = null,
        public readonly ?Model            $newModel = null,
    )
    {
        $this->s = new FilamentSelector($this->name);
        $this->currentValue = $this->currentModel->{$this->name};
        if (isset($this->newModel)) {
            // If $newModel is not provided then do not set $newValue so it throws exception instead of null.
            // Because since it is not set the developer should not use it, because it is missing some logic.
            $this->newValue = $this->newModel->{$this->name};
        }
    }

}
