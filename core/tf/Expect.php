<?php

namespace core\tf;

use Closure;

interface Expect {
    public function toBe(mixed $value): self;

    public function compare(Closure $compare): self;
}