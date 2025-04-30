<?php

namespace core\forms\controls;

use core\forms\controls\Input\Input;

class NumberField extends Input {
    public function __construct(
        string $name = self::class,
        string $label = self::class,
        string $value = ''
    ) {
        parent::__construct("number", $name, $label, $value);
    }

    public function min(float $min): self {
        $this->addAttribute("min", $min);
        return $this;
    }

    public function max(float $max): self {
        $this->addAttribute("min", $max);
        return $this;
    }

    public function step(float $step): self {
        $this->addAttribute("min", $step);
        return $this;
    }
}