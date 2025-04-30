<?php

namespace core\forms\controls;

use core\view\View;

interface Control extends View {
    public function setName(string $name): void;

    public function setLabel(string $label): void;

    public function setValue(mixed $value): void;
}