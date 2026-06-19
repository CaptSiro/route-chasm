<?php

namespace core\tf;

use Closure;
use Exception;

class Func {
    protected int $invokeCount = 0;
    protected bool $hasThrown = false;

    public function __construct(
        protected readonly Closure $fn,
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



    public function hasBeenInvoked(): bool {
        return $this->invokeCount !== 0;
    }

    public function getInvokeCount(): int {
        return $this->invokeCount;
    }

    public function reset(): void {
        $this->invokeCount = 0;
        $this->hasThrown = false;
    }
}