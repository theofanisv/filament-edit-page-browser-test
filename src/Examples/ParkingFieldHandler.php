<?php

namespace Theograms\EditPageTester\Examples;

use App\Filament\Forms\BelongsToInput;
use App\Filament\Forms\MorphToInput;
use Theograms\EditPageTester\Contracts\CustomFieldHandler;
use Theograms\EditPageTester\FilamentSelector;
use Theograms\EditPageTester\Iteration;

class ParkingFieldHandler implements CustomFieldHandler
{
    public function fill(Iteration $i): ?bool
    {
        if ($i->field instanceof BelongsToInput) {
            $this->fillBelongsToInput($i);
            return false;
        }

        if ($i->field instanceof MorphToInput) {
            $this->fillMorphToInput($i);
            return false;
        }

        return null;
    }

    public function preview(Iteration $i): ?bool
    {
        if ($i->field instanceof BelongsToInput) {
            $this->previewBelongsToInput($i);
            return false;
        }

        if ($i->field instanceof MorphToInput) {
            $this->previewMorphToInput($i);
            return false;
        }

        return null;
    }

    public function compare(Iteration $i): ?bool
    {
        if ($i->field instanceof BelongsToInput) {
            $this->compareBelongsToInput($i);
            return false;
        }

        if ($i->field instanceof MorphToInput) {
            $this->compareMorphToInput($i);
            return false;
        }

        return null;
    }

    protected function fillBelongsToInput(Iteration $i): void
    {
        /** @var BelongsToInput $field */
        $field = $i->field;
        $newRelated = $i->newModel->{$field->getRelationshipName()};
        $currentRelated = $i->currentModel->{$field->getRelationshipName()};

        if ($newRelated) {
            $i->page->click($i->s->dropdownButton());

            if ($field->isSearchable()) {
                $titleAttribute = $field->getRelationshipTitleAttribute()
                    ?? $field->getRelatedResource()::getRecordTitleAttribute();
                $i->page->type($i->s->dropdownSearch(), (string)$newRelated->{$titleAttribute});
            }

            $i->page->click($i->s->dropdownOption($newRelated->getKey()));
        } else if ($currentRelated) {
            $i->page->click($i->s->dropdownClearButton());
        }
    }

    protected function previewBelongsToInput(Iteration $i): void
    {
        /** @var BelongsToInput $field */
        $field = $i->field;
        $related = $i->currentModel->{$field->getRelationshipName()};

        if ($related) {
            $titleAttribute = $field->getRelationshipTitleAttribute()
                ?? $field->getRelatedResource()::getRecordTitleAttribute();
            $i->page->assertSeeIn($i->s->dropdownLabel(), (string)$related->{$titleAttribute});
        } else {
            $i->page->assertVisible($i->s->dropdownPlaceholder());
        }
    }

    protected function compareBelongsToInput(Iteration $i): void
    {
        /** @var BelongsToInput $field */
        $field = $i->field;
        $newRelated = $i->newModel->{$field->getRelationshipName()};
        $currentRelated = $i->currentModel->{$field->getRelationshipName()};
        $message = "Values are different after save for '{$i->name}' (" . $i->field::class . ')';

        expect($currentRelated?->getKey())->toBe($newRelated?->getKey(), $message);
    }

    protected function fillMorphToInput(Iteration $i): void
    {
        /** @var MorphToInput $field */
        $field = $i->field;
        $newRelated = $i->newModel->{$field->getRelationshipName()};
        $currentRelated = $i->currentModel->{$field->getRelationshipName()};

        $relationship = $field->getRelationship();
        $morphTypeField = $relationship->getMorphType();
        $foreignKeyField = $relationship->getForeignKeyName();

        $morphTypeSelector = new FilamentSelector($morphTypeField);
        $foreignKeySelector = new FilamentSelector($foreignKeyField);

        if ($newRelated) {
            $morphType = $newRelated::class;
            $i->page->click($morphTypeSelector->dropdownButton());
            $i->page->click($morphTypeSelector->dropdownOption($morphType));

            $i->page->click($foreignKeySelector->dropdownButton());

            if ($field->isSearchable()) {
                $titleAttribute = $this->getMorphTypeTitleAttribute($field, $morphType);
                $i->page->type($foreignKeySelector->dropdownSearch(), (string)$newRelated->{$titleAttribute});
            }

            $i->page->click($foreignKeySelector->dropdownOption($newRelated->getKey()));
        } else if ($currentRelated) {
            $i->page->click($morphTypeSelector->dropdownClearButton());
        }
    }

    protected function previewMorphToInput(Iteration $i): void
    {
        /** @var MorphToInput $field */
        $field = $i->field;
        $related = $i->currentModel->{$field->getRelationshipName()};

        $relationship = $field->getRelationship();

        $morphTypeSelector = new FilamentSelector($relationship->getMorphType());
        $foreignKeySelector = new FilamentSelector($relationship->getForeignKeyName());

        if ($related) {
            $morphType = $related::class;

            $i->page->assertSeeIn($morphTypeSelector->dropdownLabel(), $this->getMorphTypeLabel($field, $morphType));

            $titleAttribute = $this->getMorphTypeTitleAttribute($field, $morphType);
            $i->page->assertSeeIn($foreignKeySelector->dropdownLabel(), (string)$related->{$titleAttribute});
        } else {
            $i->page->assertVisible($morphTypeSelector->dropdownPlaceholder());
        }
    }

    protected function compareMorphToInput(Iteration $i): void
    {
        /** @var MorphToInput $field */
        $field = $i->field;
        $newRelated = $i->newModel->{$field->getRelationshipName()};
        $currentRelated = $i->currentModel->{$field->getRelationshipName()};
        $message = "Values are different after save for '{$i->name}' (" . $i->field::class . ')';

        expect($currentRelated?->getMorphClass())->toBe($newRelated?->getMorphClass(), $message . ' (morph type)');
        expect($currentRelated?->getKey())->toBe($newRelated?->getKey(), $message . ' (key)');
    }

    protected function getMorphTypeLabel(MorphToInput $field, string $morphType): string
    {
        foreach ($field->getTypes() as $type) {
            if ($type->getModel() === $morphType) {
                return $type->getLabel();
            }
        }

        return class_basename($morphType);
    }

    protected function getMorphTypeTitleAttribute(MorphToInput $field, string $morphType): string
    {
        foreach ($field->getTypes() as $type) {
            if ($type->getModel() === $morphType) {
                return $type->getTitleAttribute();
            }
        }

        return 'id';
    }
}
