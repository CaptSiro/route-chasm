<?php

namespace components\core\CallStack;

use core\view\Renderer;
use core\view\View;

class CallStack implements View {
    use Renderer;

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
}