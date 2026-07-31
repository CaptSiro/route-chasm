<?php

namespace core\view_old;

class XmlHeader implements View {
    public function __construct(
        protected string $version = '1.0',
        protected string $encoding = 'UTF-8',
    ) {}



    public function render(): string {
        return "<?xml version=\"$this->version\" encoding=\"$this->encoding\" ?>";
    }

    public function getRoot(): View {
        return $this;
    }

    public function __toString(): string {
        return $this->render();
    }
}