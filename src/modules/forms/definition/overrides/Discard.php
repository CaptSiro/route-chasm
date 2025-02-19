<?php

namespace modules\forms\definition\overrides;

use core\Singleton;
use core\view\View;

class Discard implements FieldDefinition {
    use Singleton;

    public function include(): bool {
        return false;
    }

    public function getComponent(mixed $value): ?View {
        return null;
    }

    public function setName(string $name): void {
    }
}