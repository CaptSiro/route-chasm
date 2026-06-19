<?php

namespace core\module;

abstract class DefaultModule implements Module {
    use AccessibleAfterLoad;

    public function load(Loader $loader): void {
        $this->markLoaded();
    }
}