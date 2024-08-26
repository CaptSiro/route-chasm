<?php

namespace sptf\structs;

use Closure;
use Exception;

class Func {
    public int $invokeCount = 0;
    public bool $hasThrown = false;



    public function __construct(
        private readonly Closure $fn,
        public bool $propagateExceptions = false
    ) {}



    /**
     * @throws Exception
     */
    public function __invoke(...$args): void {
        $this->invokeCount++;

        try {
            ($this->fn)(...$args);
        } catch (Exception $e) {
            $this->hasThrown = true;

            if ($this->propagateExceptions) {
                throw $e;
            }
        }
    }



    function hasBeenInvoked(): bool {
        return $this->invokeCount !== 0;
    }



    function reset() {
        $this->invokeCount = 0;
        $this->hasThrown = false;
    }
}