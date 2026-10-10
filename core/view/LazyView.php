<?php

namespace core\view;

use Closure;

/**
 * Defers building a view until it is actually rendered.
 *
 * The factory is called at most once; the result is memoized. Note that it is a plain View, not a Component, so a
 * non-HTML renderer set on the parent is not propagated into the deferred view.
 */
class LazyView implements View {
    private ?View $resolved = null;
    
    /**
     * @param Closure(): View $factory
     */
    public function __construct(
        private readonly Closure $factory
    ) {}



    public function resolve(): View {
        return $this->resolved ??= ($this->factory)();
    }

    public function render(): string {
        return $this->resolve()->render();
    }

    public function __toString(): string {
        return $this->render();
    }
}
