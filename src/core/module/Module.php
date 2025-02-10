<?php

namespace core\module;

interface Module {
    public function load(Loader $loader): void;

    public function isLoaded(): bool;

    public function getInfo(): ModuleInfo;

    public function migrate(string $fromVersion): void;
}
