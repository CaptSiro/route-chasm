<?php

namespace core\forms;

use core\html\Attribute;
use core\html\HtmlAttribute;

class FormAction implements Attribute {
    use HtmlAttribute;



    public const TYPE_BUTTON = "button";
    public const TYPE_RESET = "reset";
    public const TYPE_SUBMIT = "submit";



    public static function submit(string $label = "Submit"): self {
        return new self(self::TYPE_SUBMIT, $label);
    }



    protected ?string $name = null;
    protected ?string $value = null;

    /**
     * @param string $type
     * @param string $label
     */
    public function __construct(
        protected string $type,
        protected string $label,
    ) {}



    public function getType(): string {
        return $this->type;
    }

    public function getLabel(): string {
        return $this->label;
    }

    public function getName(): ?string {
        return $this->name;
    }

    public function getValue(): ?string {
        return $this->value;
    }

    public function setValue(string $name, string $value): static {
        $this->name = $name;
        $this->value = $value;
        return $this;
    }
}