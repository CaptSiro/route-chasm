<?php

namespace components\forms\controls;

use components\html\Attribute;
use components\html\HtmlAttribute;
use core\RouteChasmEnvironment;
use core\sideloader\importers\Javascript\Javascript;
use core\view\ViewTemplateRenderer;

class Select implements Control, Attribute {
    use ViewTemplateRenderer, FormControl, HtmlAttribute;

    public const DATA_ATTRIBUTE_SEARCH_FUNCTION = 'search';
    public const DATA_ATTRIBUTE_ON_OPTION_SELECTED_FUNCTION = 'on-option-selected';



    public static function importAssets(): void {
        Javascript::import(Select::getStaticResource('Select.js'));
    }



    protected bool $selectedOptionsAreEternal = false;

    /**
     * @param string $name
     * @param string $label
     * @param array<string, string> $values value => label
     * @param string|null $selected
     */
    public function __construct(
        protected string $name = self::class,
        protected string $label = self::class,
        protected array $values = [],
        protected ?string $selected = null
    ) {
        $this->setPlaceholder('Type to search');
    }



    public function getFieldName(): ?string {
        return $this->name;
    }

    public function setPlaceholder(string $placeholder): static {
        $this->addAttribute('placeholder', $placeholder);
        return $this;
    }



    // Control
    public function setName(string $name): void {
        $this->name = $name;
    }

    public function setLabel(string $label): void {
        $this->label = $label;
    }

    public function setValue(mixed $value): void {
        $this->selected = $value;
    }

    public function setValues(array $values): void {
        $this->values = $values;
    }

    public function setAsyncSearch(string $url, int $minLength = 3, string $queryArgument = RouteChasmEnvironment::QUERY_SEARCH): static {
        $this->selectedOptionsAreEternal = true;
        return $this
            ->addDataAttribute(self::DATA_ATTRIBUTE_SEARCH_FUNCTION, 'form_asyncSelect_search')
            ->addDataAttribute(self::DATA_ATTRIBUTE_ON_OPTION_SELECTED_FUNCTION, 'form_asyncSelect_onOptionSelected')
            ->addDataAttribute('search-url', $url)
            ->addDataAttribute('search-query-argument', $queryArgument)
            ->addDataAttribute('search-min-length', $minLength);
    }
}