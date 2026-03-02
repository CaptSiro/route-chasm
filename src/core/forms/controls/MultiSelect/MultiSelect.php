<?php

namespace core\forms\controls\MultiSelect;

use core\forms\controls\Control;
use core\forms\controls\FormControl;
use core\forms\controls\FormControlInfo;
use core\html\Attribute;
use core\html\HtmlAttribute;
use core\view\Renderer;

class MultiSelect implements Control, Attribute {
    use Renderer, FormControl, FormControlInfo, HtmlAttribute;

    /**
     * @param string $value
     * @return array<string>
     */
    public static function parse(string $value): array {
        return explode(';', $value);
    }



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
}