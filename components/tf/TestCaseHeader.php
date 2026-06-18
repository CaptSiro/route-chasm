<?php

namespace components\tf;

use core\tf\TestOutcome;
use core\view\FormatAble;
use core\view\FormatAbleTrait;

class TestCaseHeader implements FormatAble {
    use FormatAbleTrait;



    public function __construct(
        protected TestOutcome $outcome,
        protected string $name,
        protected float $time
    ) {}



    public function getName(): string {
        return $this->name;
    }

    public function getTime(): float {
        return $this->time;
    }

    public function getOutcome(): TestOutcome {
        return $this->outcome;
    }

    public function getTimeFormatted(): string {
        return sprintf("%.02f s", $this->time);
    }

    public function toText(): string {
        $outcome = $this->outcome->value;
        $time = $this->getTimeFormatted();

        return "[$outcome] $time $this->name";
    }

    public function jsonSerialize(): array {
        return [
            'outcome' => $this->outcome,
            'time' => $this->time,
            'name' => $this->name
        ];
    }
}