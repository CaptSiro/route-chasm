<?php

namespace patterns;

class Set implements Pattern {
    public function __construct(
        protected array $set
    ) {}



    function match(?string $value): bool {
        return !is_null($value) && in_array($value, $this->set, true);
    }

    function matchPipeline(Pipeline $pipeline, ?string &$match): bool {
        $match ??= "";

        $queue = array_keys($this->set);
        $matches = [];
        $i = 0;

        while (!empty($queue) && !$pipeline->isExhausted()) {
            $char = $pipeline->current();

            $key = array_unshift($queue);
            if ($i < strlen($this->set[$key])) {
                if ($this->set[$key] === $char) {
                    $queue[] = $key;
                }
            } else {
                $matches[] = $this->set[$key];
            }

            $pipeline->next();
            $i++;
        }

        if (empty($matches)) {
            return false;
        }

        $match = array_pop($matches);
        return true;
    }
}