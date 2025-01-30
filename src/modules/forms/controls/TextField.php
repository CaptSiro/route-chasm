<?php

namespace modules\forms\controls;

use modules\forms\controls\Input\Input;

class TextField extends Input {
    public function __construct(string $name, string $label, ?string $value = null) {
        parent::__construct("text", $name, $label, $value);
    }
}