<?php

namespace core\view;

class StringRenderer implements View {
    public function __construct(
        protected string $string
    ) {}

    public function __toString(): string {
        return $this->string;
    }



    public function render(): string {
        return $this->string;
    }

    public function getRoot(): View {
        return $this;
    }
}