<?php

namespace Theograms\EditPageTester\Concerns;

use Closure;
use Theograms\Forms\Components\Checkbox;
use Theograms\Forms\Components\CheckboxList;
use Theograms\Forms\Components\CodeEditor;
use Theograms\Forms\Components\DatePicker;
use Theograms\Forms\Components\DateTimePicker;
use Theograms\Forms\Components\Field;
use Theograms\Forms\Components\KeyValue;
use Theograms\Forms\Components\RichEditor;
use Theograms\Forms\Components\Select;
use Theograms\Forms\Components\Textarea;
use Theograms\Forms\Components\TextInput;
use Theograms\Forms\Components\TimePicker;
use Theograms\Forms\Components\Toggle;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * @mixin \Theograms\EditPageTester\EditPageTester
 */
trait ValuesComparator
{
    protected ?Closure $compareValueUsing = null;

    /**
     * If the callback returns false, the field will not be processed further.
     * @param null|Closure(string $name, Field $field, Model $current, Model $new):mixed $callback
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
            if ($this->hasCustomFieldHandler() && $this->getCustomFieldHandler()->canHandle($field)) {
                $this->getCustomFieldHandler()->compare($name, $field, $this->current->$name, $this->new->$name);
                continue;
            }

            match ($field::class) {
                Textarea::class,
                TextInput::class,
                TimePicker::class,
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
