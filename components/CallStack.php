<?php

namespace components;

use core\view\FormatAble;
use core\view\FormatAbleTrait;
use core\view\ViewTemplate;

class CallStack implements ViewTemplate, FormatAble {
    use FormatAbleTrait;

    protected array $stack;

    public function __construct(int $remove = 0) {
        $this->stack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        $count = max($remove, 0) + 1;
        for ($i = 0; $i < $count; $i++) {
            array_shift($this->stack);
        }
    }



    public function getEntryClass(array $entry): string {
        if (!isset($entry['class'])) {
            return '';
        }

        return $entry['class'] . $entry['type'];
    }



    // FormatAble
    public function jsonSerialize(): array {
        return $this->stack;
    }
}