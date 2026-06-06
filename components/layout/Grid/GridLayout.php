<?php

namespace components\layout\Grid;

use components\layout\Grid\description\GridColumn;
use core\view\View;

interface GridLayout extends View {
    public function getColumnTemplate(): string;

    public function add(string $name, string $label, string $template = '1fr'): static;

    public function addAsFirst(string $name, string $label, string $template = '1fr'): static;

    /**
     * @param array<string, GridColumn> $layout
     * @return $this
     */
    public function addAll(array $layout): static;

    public function setFooter(?View $footer): void;

    public function load(array $rows): static;
}