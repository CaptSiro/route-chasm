<?php

namespace core\collections;

use core\view\View;

class Views implements View {
    public static function from(View|string ...$views): static {
        return new self($views);
    }



    /**
     * @param array<View|string> $views
     */
    public function __construct(
        protected array $views
    ) {}



    public function render(): string {
        return implode('', $this->views);
    }

    public function getRoot(): View {
        return $this;
    }

    public function __toString(): string {
        return $this->render();
    }
}