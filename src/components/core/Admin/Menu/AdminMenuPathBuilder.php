<?php

namespace components\core\Admin\Menu;

class AdminMenuPathBuilder {
    /**
     * @var array<AdminMenuLabel>
     */
    protected array $items;

    public function __construct() {
        $this->items = [];
    }

    public function add(string $label, string $icon = ''): static {
        $this->items[] = new AdminMenuLabel($label, $icon);
        return $this;
    }

    /**
     * @return array<AdminMenuLabel>
     */
    public function build(): array {
        return $this->items;
    }
}