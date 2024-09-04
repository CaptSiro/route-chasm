<?php

namespace patterns;

use core\Pipeline;

interface Pattern {
    function match(?string $value): bool;
    function matchPipeline(Pipeline $pipeline, ?string &$match): bool;
}