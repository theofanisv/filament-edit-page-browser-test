<?php

namespace Theograms\EditPageTester\Concerns;

use ArrayAccess;
use Closure;
use Filament\Schemas\Components\Component;
use Illuminate\Support\Carbon;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Schema;
use Theograms\EditPageTester\Iteration;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\CodeEditor\Enums\Language;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
     * @param null|Closure(string $name, Field|Component $field, AwaitableWebpage $page):mixed $callback
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

    public function getDisplayValue(string $field_name, $value, mixed $default = null): mixed
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

            if (value($this->previewFieldUsing, $name, $field, $page) === false) {
                continue;
            }

            // Check if custom handler can handle this field
            $i = fn() => new Iteration($name, $field, $this->current, $this, page: $page);
            if ($this->hasCustomFieldHandler() && $this->getCustomFieldHandler()->preview($i()) === false) {
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

                ToggleButtons::class => $page->assertChecked($s->toggleButtonsItem($this->current->$name)),

                Checkbox::class => $this->current->$name
                    ? $page->assertChecked($s->checkbox())
                    : $page->assertNotChecked($s->checkbox()),

                CheckboxList::class => collect($page->script("[...document.querySelectorAll('{$s->escape($s->checkboxListItems())}')].map(el => el.value)"))
                    ->each(fn(string $item) => $this->getDisplayValue($name, $item,
                        fn() => filled($field->getRelationshipName())
                            ? in_array($item, $this->current->{$name}->modelKeys())
                            : in_array($item, $this->current->{$name})
                    )
                        ? $page->assertChecked($s->checkboxListItem($item))
                        : $page->assertNotChecked($s->checkboxListItem($item))
                    ),

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

    /**
     * Format datetime for display. Override this method to customize formatting.
     */
    protected function formatDateTime($datetime): string
    {
        if (empty($datetime)) {
            return '';
        }

        $format = Schema::make()->getDefaultDateTimeDisplayFormat();

        return (new Carbon($datetime))->translatedFormat($format);
    }

    /**
     * Format date for display. Override this method to customize formatting.
     */
    protected function formatDate($date): string
    {
        if (empty($date)) {
            return '';
        }

        $format = Schema::make()->getDefaultDateDisplayFormat();

        return (new Carbon($date))->translatedFormat($format);
    }

}
