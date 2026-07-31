<?php

namespace core\view;

class str extends Component {
    public static function view(string $string): static {
        return new self($string);
    }



    public function __construct(
        protected string $string,
    ) {
        parent::__construct();
    }



    // View
    public function render(): string {
        // Always return string, no matter the renderer
        return $this->string;
    }

    public function __toString(): string {
        return $this->render();
    }
}