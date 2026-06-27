<?php

namespace core\view2;

class str implements View {
    public static function view(string $string): View {
        return new self($string);
    }



    public function __construct(
        protected string $string
    ) {}



    // View
    public function render(): string {
        return $this->string;
    }

    public function __toString(): string {
        return $this->render();
    }
}