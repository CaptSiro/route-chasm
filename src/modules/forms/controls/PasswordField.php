<?php

namespace modules\forms\controls;

use modules\forms\controls\Input\Input;

class PasswordField extends Input {
    public function __construct(string $name, string $label, ?string $value = null) {
        parent::__construct("password", $name, $label, $value);
    }
}