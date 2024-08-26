<?php

namespace sptf\interfaces;

use Closure;

interface Expect {
    function toBe(mixed $value): self;
    function compare(Closure $compare): self;
}