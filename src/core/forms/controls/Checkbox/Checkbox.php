<?php

namespace core\forms\controls\Checkbox;

use core\forms\controls\Input\Input;

class Checkbox extends Input {
    public function __construct(
        string $name = self::class,
        string $label = self::class,
        bool $checked = false
    ) {
        parent::__construct("checkbox", $name, $label, $checked);
        if ($checked) {
            $this->addAttribute("checked", $checked);
        }

        $this->setTemplate($this->getResource("Checkbox"));
    }
}