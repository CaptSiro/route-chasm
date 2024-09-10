<?php

namespace core\module;

interface Module {
    public function load(Loader $loader): void;

    public function isLoaded(): bool;
}
