<?php

namespace core\forms\controls;

use core\forms\controls\Input\Input;

class Date extends Input {
    public function __construct(
        string $name = self::class,
        string $label = self::class,
        string $value = ''
    ) {
        parent::__construct("date", $name, $label, $value);
    }
}