<?php

namespace core;

interface Module {
    public function load(Loader $loader): void;

    public function isLoaded(): bool;
}
