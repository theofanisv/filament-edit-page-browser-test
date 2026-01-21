<?php

namespace Theograms\EditPageTester;

use Theograms\EditPageTester\Concerns\FormFiller;
use Theograms\EditPageTester\Concerns\FormViewer;
use Theograms\EditPageTester\Concerns\ValuesComparator;
use Theograms\EditPageTester\Contracts\CustomFieldHandler;
use Filament\Facades\Filament;
use Filament\Forms\Components\Field;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Pest\Browser\Api\AwaitableWebpage;

class EditPageTester
{
    use FormViewer, FormFiller, ValuesComparator;

    protected bool $verbose = false;
    protected array $cache = [];
    protected ?CustomFieldHandler $customFieldHandler = null;

    public static function make(Model $current, ?Model $new = null): static
    {
        return new static($current, $new);
    }

    public function __construct(
        protected Model  $current,
        protected ?Model $new = null,
    )
    {
    }

    /**
     * Register a custom field handler for application-specific field types.
     */
    public function withCustomFieldHandler(CustomFieldHandler $handler): static
    {
        $this->customFieldHandler = $handler;
        return $this;
    }

    protected function hasCustomFieldHandler(): bool
    {
        return $this->customFieldHandler !== null;
    }

    protected function getCustomFieldHandler(): CustomFieldHandler
    {
        return $this->customFieldHandler;
    }

    /**
     * @return class-string
     */
    protected function getResource(): string
    {
        return Filament::getModelResource($this->current);
    }

    protected function getEditPageUrl(): string
    {
        return $this->getEditPage()::getUrl(['record' => $this->current], false);
    }

    /**
     * @return class-string<EditRecord>
     */
    protected function getEditPage(): string
    {
        return $this->cache[__FUNCTION__] ??= $this->getResource()::getPages()['edit']->getPage();
    }

    protected function getSchema(): Schema
    {
        return $this->cache[__FUNCTION__] ??= (function () {
            $livewire = new ($this->getEditPage());
            $livewire->form($schema = new Schema($livewire));
            $schema->model($this->current); // Used by MorphToSelect
            return $schema;
        })();
    }

    /**
     * @return Collection<string,Field>
     */
    protected function getFields(): Collection
    {
        return collect($this->getSchema()->getFlatFields());
    }

    public function requiredVisibleFields(array $fields): static
    {
        $untested = array_diff($fields, $this->getFields()->keys()->all());
        expect($untested)->toBeEmpty($this->getEditPage() . ' Required fields not visible: ' . implode(', ', $untested));

        return $this;
    }

    public function verbose(bool $verbose = true): static
    {
        $this->verbose = $verbose;
        return $this;
    }

    protected function verboseLog(string $message): void
    {
        if ($this->verbose) {
            static $terminal = terminal();
            $terminal->writeln($message);
        }
    }

    public function testSave(): AwaitableWebpage
    {
        $page = $this->fillFormAndSubmit();

        $this->current->refresh();

        $this->compareModels();

        return $page;
    }

    /**
     * Selector helper for filament fields.
     */
    protected function s(string $name): FilamentSelector
    {
        return new FilamentSelector($name);
    }
}
