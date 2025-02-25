<?php

namespace components\core\Admin\Menu;

readonly class AdminMenuLabel {
    public function __construct(
        protected string $label,
        protected string $icon = ''
    ) {}

    public function getLabel(): string {
        return $this->label;
    }

    public function hasIcon(): bool {
        return $this->icon !== '';
    }

    public function getIcon(): string {
        return $this->icon;
    }
}