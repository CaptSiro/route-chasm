<?php

namespace core\module;

class DefaultModule implements Module {
    use AccessibleAfterLoad;

    public function load(Loader $loader): void {
        $this->markLoaded();
    }
}