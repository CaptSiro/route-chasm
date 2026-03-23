<?php

namespace core\forms\controls\Select;

use core\forms\controls\Control;
use core\forms\controls\FormControl;
use core\html\Attribute;
use core\html\HtmlAttribute;
use core\view\Renderer;

class Select implements Control, Attribute {
    use Renderer, FormControl, HtmlAttribute;

    public const DATA_ATTRIBUTE_SEARCH_FUNCTION = 'search';



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

    public function setAsyncSearch(string $url, string $queryArgument = 'q', int $minLength = 3): static {
        return $this
            ->addDataAttribute(self::DATA_ATTRIBUTE_SEARCH_FUNCTION, 'form_select_searchAsync')
            ->addDataAttribute('search-url', $url)
            ->addDataAttribute('search-query-argument', $queryArgument)
            ->addDataAttribute('search-min-length', $minLength);
    }
}