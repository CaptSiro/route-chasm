<?php

namespace modules\forms\controls\Input;

use core\Render;
use core\TemplateRenderer;
use core\utils\Arrays;
use modules\forms\controls\Control;
use modules\forms\controls\FormControl;

class Input implements Render, Control {
    use TemplateRenderer, FormControl;

    protected array $attributes;
    protected string $cssClass;



    public function __construct(
        protected string $type,
        protected string $name,
        protected string $label,
        protected ?string $value = null,
    ) {
        $this->cssClass = "";
        $this->attributes = [];
        $this->setTemplate(self::getStaticSource("Input.phtml"));
    }



    public function addCssClass(string $class): self {
        if ($this->cssClass === "") {
            $this->cssClass = $class;
            return $this;
        }

        $this->cssClass .= ' '. $class;
        return $this;
    }

    public function getFieldName(): ?string {
        return $this->name;
    }

    public function getId(): string {
        if (is_null($this->context)) {
            return $this->name;
        }

        return $this->context->createId($this->name);
    }

    public function addAttribute(string $name, mixed $value): self {
        $this->attributes[$name] = $value;
        return $this;
    }

    public function getAttributes(): string {
        return Arrays::htmlEncode($this->attributes);
    }

    public function getAttribute(string $name): mixed {
        return $this->attributes[$name] ?? null;
    }

    public function pattern(string $pattern): self {
        $this->addAttribute("pattern", $pattern);
        return $this;
    }

    public function required(): self {
        $this->addAttribute("required", true);
        return $this;
    }

    public function validate(?string $input, string &$reason): bool {
        if (is_null($input)) {
            if ($this->getAttribute("required") === true) {
                $reason = "Field $this->label is required";
                return false;
            }

            $input = "";
        }

        $pattern = $this->getAttribute("pattern");
        if (!is_null($pattern) && !preg_match($pattern, $input)) {
            $reason = "Field $this->label has invalid value";
            return false;
        }

        return true;
    }
}