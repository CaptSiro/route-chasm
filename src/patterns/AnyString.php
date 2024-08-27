<?php

namespace patterns;

use core\Singleton;

class AnyString implements Pattern {
    use Singleton;

    protected int $minLength;



    public function __construct() {
        $this->minLength = 0;
    }

    /**
     * @param int $minLength
     * @return self
     */
    public function setMinLength(int $minLength): self {
        $this->minLength = $minLength;
        return $this;
    }



    function match(?string $value): bool {
        return !is_null($value) && strlen($value) >= $this->minLength;
    }

    function matchPipeline(Pipeline $pipeline, ?string &$match): bool {
        $match ??= "";

        while (!$pipeline->isExhausted()) {
            $match .= $pipeline->current();
            $pipeline->next();
        }

        return strlen($match) >= $this->minLength;
    }
}