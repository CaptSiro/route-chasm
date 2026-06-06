<?php

namespace components\forms\controls;

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