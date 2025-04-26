<?php

namespace models\extensions\Enable;

interface Enable {
    public function isEnabled(): bool;

    public function enable(bool $enable = true): void;

    public function disable(): void;
}