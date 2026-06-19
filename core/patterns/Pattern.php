<?php

namespace core\patterns;

use core\Pipeline;

interface Pattern {
    public function __toString(): string;

    public function match(?string $value): bool;

    public function matchPipeline(Pipeline $pipeline, ?string &$match): bool;
}