<?php

namespace Theograms\EditPageTester\Concerns;

use Closure;
use Filament\Forms\Components\ToggleButtons;
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
use Filament\Schemas\Components\Component;
use Illuminate\Support\Collection;
use Pest\Browser\Api\AwaitableWebpage;
use Pest\Browser\Api\Webpage;
use RuntimeException;
use Theograms\EditPageTester\Iteration;

/**
 * @mixin \Theograms\EditPageTester\EditPageTester
 */
trait FormFiller
{
    protected ?Closure $fillFieldUsing = null;

    /**
     * If the callback returns false, the field will not be processed further.
     * @param null|Closure(string $name, Field|Component $field, AwaitableWebpage $page):mixed $callback
     */
    public function fillFieldUsing(?Closure $callback): static
    {
        $this->fillFieldUsing = $callback;
        return $this;
    }

    /**
     * @return AwaitableWebpage|Webpage
     */
    public function fillFormAndSubmit(): AwaitableWebpage
    {
        $page = visit($this->getEditPageUrl())
            ->assertNoJavascriptErrors();

        foreach ($this->getFields() as $name => $field) {
            $this->verboseLog('* <comment>' . class_basename($this->getEditPage()) . "</comment> testing fill: <info>$name</info> (" . str_after($field::class, 'Filament\Forms\Components\\') . ')');

            if (value($this->fillFieldUsing, $name, $field, $page) === false) {
                continue;
            }

            // Check if custom handler can handle this field
            $i = fn() => new Iteration($name, $field, $this->current, $this, page: $page, newModel: $this->new);
            if ($this->hasCustomFieldHandler() && $this->getCustomFieldHandler()->fill($i()) === false) {
                continue;
            }

            $s = $this->s($name);

            match ($field::class) {
                Textarea::class,
                TextInput::class => $page->type($s->input(), (string)$this->new->$name),

                Select::class => [
                    $page->click($s->dropdownButton()),
                    $field->isSearchable()
                        ? $page->type($s->dropdownSearch(), $this->getDisplayValue($name, $this->new->$name, fn() => $field->getOptions()[$this->new->$name]))
                        : null,
                    $page->click($s->dropdownOption($this->new->$name)),
                ],

                RichEditor::class => $page->type($s->richText(), $this->new->$name),

                DateTimePicker::class => ($datetime = $this->new->$name)
                    ? $page
                        ->click($s->datetimeTrigger())
                        ->type($s->datetimeYearInput(), (string)$datetime->year)
                        ->select($s->datetimeMonthSelect(), $datetime->month - 1)
                        ->type($s->datetimeHourInput(), (string)$datetime->hour)
                        ->type($s->datetimeMinuteInput(), (string)$datetime->minute)
                        ->type($s->datetimeSecondInput(), (string)$datetime->second)
                        ->wait(0.3) // Delay is needed (unreasonably).
                        ->click($s->datetimeDayDiv($datetime->day))
                    : $page->keys($s->input(), 'Backspace'),

                DatePicker::class => ($date = $this->new->$name)
                    ? $page
                        ->click($s->datetimeTrigger())
                        ->type($s->datetimeYearInput(), (string)$date->year)
                        ->select($s->datetimeMonthSelect(), $date->month - 1)
                        ->click($s->datetimeDayDiv($date->day))
                    : $page->keys($s->input(), 'Backspace'),


                Toggle::class => str($page->attribute($s->toggleButton(), 'aria-checked'))->toBoolean() !== (bool)$this->new->$name
                    ? $page->click($s->toggleButton())
                    : null,

                ToggleButtons::class => $page->click($s->labelFor($page->attribute($s->toggleButtonsItem($this->new->$name), 'id'))),

                Checkbox::class => $this->new->$name
                    ? $page->check($s->checkbox())
                    : $page->uncheck($s->checkbox()),

                CheckboxList::class => collect($page->script("[...document.querySelectorAll('{$s->escape($s->checkboxListItems())}')].map(el => el.value)"))
                    ->each(
                        fn(string $item) => $this->getDisplayValue($name, $item,
                            fn() => filled($field->getRelationshipName())
                                ? in_array($item, $this->new->$name->modelKeys())
                                : in_array($item, collect($this->new->$name)->toArray())
                        )
                            ? $page->check($s->checkboxListItem($item))
                            : $page->uncheck($s->checkboxListItem($item))
                    ),

                CodeEditor::class => [
                    $page->clear($s->codeEditor()),
                    $field->getLanguage() === Language::Json
                        ? $page->type($s->codeEditor(), json_encode($this->new->$name))
                        : $page->type($s->codeEditor(), (string)$this->new->$name),
                ],

                KeyValue::class => [
                    Collection::times(
                        $page->script("document.querySelectorAll('{$s->escape($s->keyValueRows())}').length"),
                        fn() => $page->click($s->keyValueDeleteRowButton())
                    ),
                    Collection::times(
                        ($array = collect(is_string($array = $this->new->$name) ? json_decode($array, true) : $array))->count() - 1,
                        fn() => $page->click($s->keyValueAddRowButton())
                    ),
                    $array->keys()
                        ->each(fn($key, $row) => [
                            $page->typeSlowly($s->keyValueKeyInput($row + 1), (string)$key),
                            $page->typeSlowly($s->keyValueValueInput($row + 1), (string)$array[$key]),
                        ]),
                ],

                default => throw new RuntimeException('Component ' . $field::class . " ($name) is not testable for filling, found on {$this->getEditPage()}."),
            };
        }

        return $page->press('.fi-main [type=submit]')
            ->assertPathIsNot($this->getEditPageUrl()); // We need to wait for the data to be persisted, otherwise it continues too quickly, and the changes are not yet saved.
    }

}
