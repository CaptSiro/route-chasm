<?php

namespace components\layout\Grid;

readonly class ColumnLayout {
    public function __construct(
        public string $label,
        public string $template = '1fr'
    ) {}
}