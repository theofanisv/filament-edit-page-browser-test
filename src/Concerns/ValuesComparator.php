<?php

namespace Theograms\EditPageTester\Concerns;

use Closure;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\CodeEditor;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Component;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use RuntimeException;
use Theograms\EditPageTester\Iteration;

/**
 * @mixin \Theograms\EditPageTester\EditPageTester
 */
trait ValuesComparator
{
    protected ?Closure $compareValueUsing = null;

    /**
     * If the callback returns false, the field will not be processed further.
     * @param null|Closure(string $name, Field|Component $field, Model $current, Model $new):mixed $callback
     */
    public function compareValueUsing(?Closure $callback): static
    {
        $this->compareValueUsing = $callback;
        return $this;
    }

    public function withNew(Model $new): static
    {
        $this->new = $new;
        return $this;
    }

    protected function compareModels(): void
    {
        throw_unless($this->new, 'New record not provided.');

        foreach ($this->getFields() as $name => $field) {
            $this->verboseLog('* <comment>' . class_basename($this->getEditPage()) . "</comment> testing saved: <info>$name</info> (" . str_after($field::class, 'Filament\Forms\Components\\') . ')');
            $message = "Values are different after save for '$name' on {$this->getEditPage()} (" . $field::class . ')';

            if (value($this->compareValueUsing, $name, $field, $this->current, $this->new) === false) {
                continue;
            }

            // Check if custom handler can handle this field
            $i = fn() => new Iteration($name, $field, $this->current, $this, newModel: $this->new);
            if ($this->hasCustomFieldHandler() && $this->getCustomFieldHandler()->compare($i()) === false) {
                continue;
            }

            match ($field::class) {
                Textarea::class,
                TextInput::class,
                TimePicker::class,
                ToggleButtons::class,
                Select::class => expect($this->current->$name)->toEqual($this->new->$name, $message),

                CodeEditor::class,
                KeyValue::class => expect($this->current->$name)->toEqualCanonicalizing($this->new->$name, $message),

                CheckboxList::class => filled($field->getRelationshipName())
                    ? expect($this->current->$name->pluck($field->getRelationshipTitleAttribute())->toArray())->toEqualCanonicalizing($this->new->$name->pluck($field->getRelationshipTitleAttribute())->toArray(), $message)
                    : expect($this->current->$name)->toEqualCanonicalizing($this->new->$name, $message),

                Checkbox::class,
                Toggle::class => expect((bool)$this->current->$name)->toBe((bool)$this->new->$name, $message),

                RichEditor::class => expect(Str::between($this->current->$name, '<p>', '</p>'))->toBe($this->new->$name, $message),

                DateTimePicker::class,
                DatePicker::class => expect($this->current->$name?->toDateTimeString())->toBe($this->new->$name?->toDateTimeString(), $message),

                default => throw new RuntimeException('Field ' . $field::class . " ($name) is not testable for comparison, found on {$this->getEditPage()}."),
            };
        }
    }
}
