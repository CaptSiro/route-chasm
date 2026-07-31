<?php

namespace components\forms\controls;

use components\html\Attribute;
use components\html\HtmlAttribute;
use core\RouteChasmEnvironment;
use core\sideloader\importers\Javascript\Javascript;
use core\view\ViewTemplateRenderer;

class MultiSelect implements Control, Attribute {
    use ViewTemplateRenderer, FormControl, FormControlInfo, HtmlAttribute;



    public const DATA_ATTRIBUTE_SEARCH_FUNCTION = Select::DATA_ATTRIBUTE_SEARCH_FUNCTION;
    public const DATA_ATTRIBUTE_ON_OPTION_SELECTED_FUNCTION = Select::DATA_ATTRIBUTE_ON_OPTION_SELECTED_FUNCTION;
    public const DATA_ATTRIBUTE_ON_OPTION_DESELECTED_FUNCTION = 'on-option-deselected';



    public static function importAssets(): void {
        Select::importAssets();
        Javascript::import(MultiSelect::getStaticResource('MultiSelect.js'));
    }



    /**
     * @param string $value
     * @return array<string>
     */
    public static function parse(string $value): array {
        return array_values(array_filter(
            explode(';', $value),
            fn(string $item) => $item !== ''
        ));
    }



    protected bool $selectedOptionsAreEternal = false;

    /**
     * @param string $name
     * @param string $label
     * @param array $values `[value => label]` pairs of values and labels
     * @param array $selected `[value]` array of selected values
     */
    public function __construct(
        protected string $name = self::class,
        protected string $label = self::class,
        protected array $values = [],
        protected array $selected = []
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

    public function setAsyncSearch(string $url, int $minLength = 3, string $queryArgument = RouteChasmEnvironment::QUERY_SEARCH): static {
        $this->selectedOptionsAreEternal = true;
        return $this
            ->addDataAttribute(self::DATA_ATTRIBUTE_SEARCH_FUNCTION, 'form_asyncSelect_search')
            ->addDataAttribute(self::DATA_ATTRIBUTE_ON_OPTION_SELECTED_FUNCTION, 'form_asyncMultiSelect_onOptionSelected')
            ->addDataAttribute(self::DATA_ATTRIBUTE_ON_OPTION_DESELECTED_FUNCTION, 'form_multiSelect_onOptionDeselected')
            ->addDataAttribute('search-url', $url)
            ->addDataAttribute('search-query-argument', $queryArgument)
            ->addDataAttribute('search-min-length', $minLength);
    }
}
