<?php

namespace Theograms\EditPageTester;

class FilamentSelector
{
    public function __construct(public string $name)
    {
    }

    /**
     * Escape attribute data `[attribute="data"]`.
     */
    public function escape(string $selector): string
    {
        return str_replace(['\\', '.', ':'], ['\\\\', '\\.', '\\:'], $selector);
    }

    protected function wirePartial(?string $name = null): string
    {
        return $this->escape('[wire:partial="schema-component::form.' . ($name ?? $this->name) . '"]');
    }

    /**
     * Wrap the name of the dropdown as proper html selector to be used in `$page->assertSeeIn`;
     */
    public function dropdownLabel(): string
    {
        return $this->wirePartial() . ' .fi-select-input-value-label';
    }

    public function dropdownPlaceholder(): string
    {
        return $this->wirePartial() . ' .fi-select-input-placeholder';
    }

    public function dropdownButton(): string
    {
        return $this->wirePartial() . ' .fi-select-input-btn';
    }

    public function dropdownClearButton(): string
    {
        return $this->wirePartial() . ' .fi-select-input-value-remove-btn';
    }

    public function dropdownSearch(): string
    {
        return $this->wirePartial() . ' .fi-select-input-search-ctn input';
    }

    public function dropdownOption(string $key): string
    {
        return $this->wirePartial() . ' .fi-dropdown-panel ' . $this->escape("[data-value=\"$key\"]");
    }

    /**
     * Wrap the name of the input as proper html selector to be used in `$page->assertValue`;
     */
    public function input(): string
    {
        return $this->idSelector();
    }

    public function textarea(): string
    {
        return $this->idSelector();
    }

    /**
     * Wrap the name of the dropdown as proper html selector to be used in `$page->assertSeeIn`;
     */
    public function richText(): string
    {
        return $this->wirePartial() . ' .tiptap';
    }

    public function datetimeTrigger(): string
    {
        return $this->wirePartial() . ' button.fi-fo-date-time-picker-trigger';
    }

    public function datetimeDayDiv(?int $day = null): string
    {
        return $this->wirePartial() . ' .fi-fo-date-time-picker-panel .fi-fo-date-time-picker-calendar div:nth-child(' . ($day ?? 'n') . ' of .fi-fo-date-time-picker-calendar-day)';
    }

    public function datetimeMonthSelect(): string
    {
        return $this->wirePartial() . ' .fi-fo-date-time-picker-panel select.fi-fo-date-time-picker-month-select';
    }

    public function datetimeYearInput(): string
    {
        return $this->wirePartial() . ' .fi-fo-date-time-picker-panel input.fi-fo-date-time-picker-year-input';
    }

    public function datetimeHourInput(): string
    {
        return $this->wirePartial() . ' .fi-fo-date-time-picker-panel .fi-fo-date-time-picker-time-inputs input:nth-of-type(1)';
    }

    public function datetimeMinuteInput(): string
    {
        return $this->wirePartial() . ' .fi-fo-date-time-picker-panel .fi-fo-date-time-picker-time-inputs input:nth-of-type(2)';
    }

    public function datetimeSecondInput(): string
    {
        return $this->wirePartial() . ' .fi-fo-date-time-picker-panel .fi-fo-date-time-picker-time-inputs input:nth-of-type(3)';
    }

    public function toggleButton(): string
    {
        return $this->idSelector();
    }

    public function checkbox(): string
    {
        return $this->idSelector();
    }

    public function checkboxListItem(string $option): string
    {
        return $this->wirePartial() . " input[type=checkbox][value=\"$option\"]";
    }

    public function checkboxListItems(): string
    {
        return $this->wirePartial() . ' input[type=checkbox]';
    }

    public function radioInput(string $value): string
    {
        return $this->wirePartial() . " input[type=radio][value=\"$value\"]";
    }

    public function toggleButtonsItem(string $value): string
    {
        return $this->radioInput($value);
    }

    public function labelFor(string $id): string
    {
        return 'label[for="' . $this->escape($id) . '"]';
    }

    protected function idSelector(): string
    {
        return '#' . str_replace(['.', ':', '-', '>'], ['\\.', '\\:', '\\-', '\\>'], "form.{$this->name}");
    }

    public function codeEditor(): string
    {
        return $this->wirePartial() . ' .cm-editor .cm-content';
    }

    public function sectionHeader(?bool $collapsed = null): string
    {
        $extra = match ($collapsed) {
            true => '.fi-collapsed',
            false => ':not(.fi-collapsed)',
            default => null,
        };

        return $this->wirePartial() . " section$extra header";
    }

    public function keyValueTable(): string
    {
        return $this->wirePartial() . ' .fi-fo-key-value-table';
    }

    /**
     * @param int $row Starts from 1
     */
    public function keyValueKeyInput(int $row = 1): string
    {
        return $this->wirePartial() . " .fi-fo-key-value-table tbody tr:nth-of-type($row) td:nth-of-type(1) input";
    }

    public function keyValueRows(): string
    {
        return $this->wirePartial() . ' .fi-fo-key-value-table tbody tr';
    }

    /**
     * @param int $row Starts from 1
     */
    public function keyValueValueInput(int $row = 1): string
    {
        return $this->wirePartial() . " .fi-fo-key-value-table tbody tr:nth-of-type($row) td:nth-of-type(2) input";
    }

    /**
     * @param int $row Starts from 1
     */
    public function keyValueDeleteRowButton(int $row = 1): string
    {
        return $this->wirePartial() . " .fi-fo-key-value-table tbody tr:nth-of-type($row) td:nth-of-type(3) button[aria-label=\"Delete row\"]";
    }

    public function keyValueAddRowButton(): string
    {
        return $this->wirePartial() . ' .fi-input-wrp-content-ctn .fi-fo-key-value-add-action-ctn button';
    }
}
