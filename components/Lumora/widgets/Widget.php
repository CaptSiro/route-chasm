<?php

namespace components\Lumora\widgets;

interface Widget {
    public function exportClass(): string;

    public function isVisible(): bool;

    public function getIcon(): string;

    public function getName(): string;

    public function getCategory(): string;

    public function getScript(): string;

    public function getStyles(): string;

    /**
     * @return array<Widget>
     */
    public function getDependencies(): array;
}