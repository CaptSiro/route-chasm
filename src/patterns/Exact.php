<?php

namespace patterns;

use core\Pipeline;

class Exact implements Pattern {
    public function __construct(
        protected string $match
    ) {}



    function match(?string $value): bool {
        return $value === $this->match;
    }

    function matchPipeline(Pipeline $pipeline, ?string &$match): bool {
        $match ??= "";

        $len = strlen($this->match);

        for ($i = 0; $i < $len && !$pipeline->isExhausted(); $i++) {
            $char = $pipeline->current();
            if ($this->match[$i] === $char) {
                $match .= $char;
                $pipeline->next();
            } else {
                return false;
            }
        }

        return strlen($this->match) === strlen($match);
    }
}