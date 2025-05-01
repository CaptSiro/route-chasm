<?php

namespace core\forms\controls\PasswordField;

use core\forms\controls\Input\Input;

class PasswordField extends Input {
    public function __construct(
        string $name = self::class,
        string $label = self::class,
        string $value = '',
        protected bool $addVisibilityControl = false
    ) {
        parent::__construct("password", $name, $label, $value);
        $this->setTemplate($this->getResource('PasswordField.phtml'));
    }
}