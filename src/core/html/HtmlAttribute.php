<?php

namespace core\html;

use core\utils\Arrays;

trait HtmlAttribute {
    protected array $attributes = [];
    protected string $cssClass = "";



    public function addAttribute(string $name, mixed $value = null): static {
        $this->attributes[$name] = $value ?? $name;
        return $this;
    }

    public function addDataAttribute(string $name, mixed $value = null): static {
        return $this->addAttribute('data-'. $name, $value);
    }

    public function getAttributes(): array {
        return $this->attributes;
    }

    public function stringifyAttributes(): string {
        return Arrays::htmlEncode($this->attributes);
    }

    public function getAttribute(string $name): mixed {
        return $this->attributes[$name] ?? null;
    }

    public function addJavascriptInit(string $function): static {
        $attr = $this->attributes['x-init'] ?? '';
        if ($attr !== '') {
            $attr .= ',';
        }

        $this->attributes['x-init'] = $attr . $function;
        return $this;
    }




    public function addCssClass(string $class): static {
        if ($this->cssClass === "") {
            $this->cssClass = $class;
            return $this;
        }

        $this->cssClass .= ' '. $class;
        return $this;
    }

    public function getCssClass(): string {
        return $this->cssClass;
    }
}