<?php

namespace modules\forms\definition\overrides;

use core\view\View;

interface FieldDefinition {
    public function include(): bool;

    public function setName(string $name): void;

    public function getComponent(mixed $value): ?View;
}