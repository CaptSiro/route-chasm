<?php

namespace components\html;

use core\utils\Arrays;

trait HtmlAttribute {
    protected array $attributes = [];
    protected string $cssClass = "";



    public function hasAttribute(string $name): bool {
        return isset($this->attributes[$name]);
    }

    public function hasDataAttribute(string $name): bool {
        return $this->hasAttribute('data-'. $name);
    }

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

    public function removeAttribute(string $name): static {
        unset($this->attributes[$name]);
        return $this;
    }

    public function removeDataAttribute(string $name): static {
        return $this->removeAttribute('data-'. $name);
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