<?php

namespace core\forms\controls\MultiSelect;

use core\forms\controls\Control;
use core\forms\controls\FormControl;
use core\forms\controls\FormControlInfo;
use core\forms\controls\Select\Select;
use core\html\Attribute;
use core\html\HtmlAttribute;
use core\RouteChasmEnvironment;
use core\view\Renderer;

class MultiSelect implements Control, Attribute {
    use Renderer, FormControl, FormControlInfo, HtmlAttribute;



    public const DATA_ATTRIBUTE_SEARCH_FUNCTION = Select::DATA_ATTRIBUTE_SEARCH_FUNCTION;
    public const DATA_ATTRIBUTE_ON_OPTION_SELECTED_FUNCTION = Select::DATA_ATTRIBUTE_ON_OPTION_SELECTED_FUNCTION;
    public const DATA_ATTRIBUTE_ON_OPTION_DESELECTED_FUNCTION = 'on-option-deselected';



    /**
     * @param string $value
     * @return array<string>
     */
    public static function parse(string $value): array {
        return explode(';', $value);
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