<?php

namespace Theograms\EditPageTester\Concerns;

use ArrayAccess;
use Closure;
use Theograms\EditPageTester\Iteration;
use Theograms\Forms\Components\Checkbox;
use Theograms\Forms\Components\CheckboxList;
use Theograms\Forms\Components\CodeEditor;
use Theograms\Forms\Components\CodeEditor\Enums\Language;
use Theograms\Forms\Components\DatePicker;
use Theograms\Forms\Components\DateTimePicker;
use Theograms\Forms\Components\Field;
use Theograms\Forms\Components\KeyValue;
use Theograms\Forms\Components\RichEditor;
use Theograms\Forms\Components\Select;
use Theograms\Forms\Components\Textarea;
use Theograms\Forms\Components\TextInput;
use Theograms\Forms\Components\Toggle;
use Pest\Browser\Api\AwaitableWebpage;
use Pest\Browser\Api\Webpage;
use RuntimeException;

/**
 * @mixin \Theograms\EditPageTester\EditPageTester
 */
trait FormViewer
{
    protected ?Closure $previewFieldUsing = null;
    protected array $value_display_map = [];


    /**
     * If the callback returns false, the field will not be processed further.
     * @param null|Closure(string $name, Field $field, AwaitableWebpage $page):mixed $callback
     */
    public function previewFieldUsing(?Closure $callback): static
    {
        $this->previewFieldUsing = $callback;
        return $this;
    }

    /**
     * @param ArrayAccess $map Value => Display value
     */
    public function withValuesDisplayMap(string $field_name, ArrayAccess $map): static
    {
        $this->value_display_map[$field_name] = $map;
        return $this;
    }

    protected function getDisplayValue(string $field_name, $value, mixed $default = null): mixed
    {
        $display = data_get($this->value_display_map[$field_name] ?? [], $value, $default);

        $this->verboseLog("Field <comment>$field_name</comment> display value: <info>" . json_encode($value) . "</info> → <info>" . json_encode($display) . "</info>");

        return $display;
    }

    /**
     * @return AwaitableWebpage|Webpage
     */
    public function testPreview(): AwaitableWebpage
    {
        $page = visit($this->getEditPageUrl())
            ->assertNoJavascriptErrors();

        foreach ($this->getFields() as $name => $field) {
            $this->verboseLog('* <comment>' . class_basename($this->getEditPage()) . "</comment> testing preview: <info>$name</info> (" . str_after($field::class, 'Filament\Forms\Components\\') . ')');
            $s = $this->s($name);
            $i = new Iteration($name, $field, $page, $s);
            if (value($this->previewFieldUsing, $name, $field, $page) === false) {
                continue;
            }

            // Check if custom handler can handle this field
            if ($this->hasCustomFieldHandler() && $this->getCustomFieldHandler()->canHandle($field)) {
                $this->getCustomFieldHandler()->preview($name, $field, $page, $this->current->$name);
                continue;
            }

            match ($field::class) {
                Textarea::class,
                TextInput::class => $page->assertValue($s->input(), (string)$this->current->$name),

                Select::class => empty($this->current->$name)
                    ? $page->assertVisible($s->dropdownPlaceholder())
                    : $page->assertSeeIn($s->dropdownLabel(), $this->getDisplayValue($name, $this->current->$name, fn() => $page->text($s->dropdownOption($this->current->$name)))),

                RichEditor::class => $page->assertSeeIn($s->richText(), $this->current->$name),

                DateTimePicker::class => $page->assertValue($s->input(), $this->formatDateTime($this->current->$name)),

                DatePicker::class => $page->assertValue($s->input(), $this->formatDate($this->current->$name)),

                Toggle::class => $page->assertAttribute($s->toggleButton(), 'aria-checked', $this->current->$name ? 'true' : 'false'),

                Checkbox::class => $this->current->$name
                    ? $page->assertChecked($s->checkbox())
                    : $page->assertNotChecked($s->checkbox()),

                CheckboxList::class => $this->testViewCheckboxList($i),

                CodeEditor::class => $field->getLanguage() === Language::Json
                    ? expect($page->text($s->codeEditor()))->json()->toEqualCanonicalizing($this->current->$name)
                    : expect($page->text($s->codeEditor()))->toEqual($this->current->$name),

                KeyValue::class => [
                    $page->assertVisible($s->keyValueTable()),
                    collect($array = is_string($array = $this->current->$name) ? json_decode($array, true) : $array)
                        ->keys()
                        ->each(fn($key, $row) => [
                            $page->assertValue($s->keyValueKeyInput($row + 1), (string)$key),
                            $page->assertValue($s->keyValueValueInput($row + 1), (string)$array[$key]),
                        ]),
                ],

                default => throw new RuntimeException('Field ' . $field::class . " ($name) is not testable for preview, found on {$this->getEditPage()}."),
            };
        }

        return $page;
    }

    protected function testViewCheckboxList(Iteration $i): void
    {
        collect($i->page->script("[...document.querySelectorAll('{$i->s->escape($i->s->checkboxListItems())}')].map(el => el.value)"))
            ->each(fn(string $item) => $this->getDisplayValue($i->name, $item,
                fn() => filled($i->field->getRelationshipName())
                    ? in_array($item, $this->current->{$i->name}->modelKeys())
                    : in_array($item, $this->current->{$i->name})
            )
                ? $i->page->assertChecked($i->s->checkboxListItem($item))
                : $i->page->assertNotChecked($i->s->checkboxListItem($item))
            );
    }

    /**
     * Format datetime for display. Override this method to customize formatting.
     */
    protected function formatDateTime($datetime): string
    {
        // Default implementation - override in your application
        return $datetime?->format('Y-m-d H:i:s') ?? '';
    }

    /**
     * Format date for display. Override this method to customize formatting.
     */
    protected function formatDate($date): string
    {
        // Default implementation - override in your application
        return $date?->format('Y-m-d') ?? '';
    }

}
