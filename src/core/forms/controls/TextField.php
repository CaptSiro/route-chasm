<?php

namespace core\forms\controls;

use core\forms\controls\Input\Input;

class TextField extends Input {
    public function __construct(
        string $name = self::class,
        string $label = self::class,
        string $value = ''
    ) {
        parent::__construct("text", $name, $label, $value);
    }
}