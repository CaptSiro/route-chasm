<?php

namespace modules\forms\controls\Checkbox;

use modules\forms\controls\Input\Input;

class Checkbox extends Input {
    public function __construct(string $name, string $label, bool $checked = false) {
        parent::__construct("checkbox", $name, $label, $checked);
        $this->addAttribute("checked", $checked);
        $this->setTemplate($this->getSource("Checkbox"));
    }
}