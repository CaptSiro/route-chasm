<?php

namespace patterns;

use core\Pipeline;

class Charset implements Pattern {
    protected array $ranges;
    protected array $set;



    public function __construct() {
        $this->ranges = [];
        $this->set = [];
    }



    public function addRange(string $from, string $to): self {
        $this->ranges[] = [ord($from), ord($to)];
        return $this;
    }

    public function add(string $character): self {
        if (!in_array($character, $this->set)) {
            $this->set[] = $character;
        }

        return $this;
    }

    public function contains(string $character): bool {
        $code = ord($character);

        foreach ($this->ranges as $range) {
            if ($range[0] <= $code && $range[1] >= $code) {
                return true;
            }
        }

        return in_array($character, $this->set);
    }

    function match(?string $value): bool {
        if (is_null($value)) {
            return false;
        }

        $len = strlen($value);

        for ($i = 0; $i < $len; $i++) {
            if (!$this->contains($value[$i])) {
                return false;
            }
        }

        return true;
    }

    function matchSequence(Pipeline $pipeline, int $length, ?string &$match): bool {
        $match ??= "";

        for ($i = 0; $i < $length && !$pipeline->isExhausted(); $i++) {
            $char = $pipeline->current();
            if (!$this->contains($char)) {
                break;
            }

            $match .= $char;
            $pipeline->next();
        }

        return strlen($match) !== 0;
    }

    function matchPipeline(Pipeline $pipeline, ?string &$match): bool {
        return $this->matchSequence($pipeline, PHP_INT_MAX, $match);
    }

    function asString(): string {
        $literal = implode('', $this->set);

        foreach ($this->ranges as $range) {
            for ($i = $range[0]; $i <= $range[1]; $i++) {
                $literal .= chr($i);
            }
        }

        return $literal;
    }
}