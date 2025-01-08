<?php

namespace core;

trait CssClass {
    protected string $cssClass = "";

    public function addCssClass(string $class): self {
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