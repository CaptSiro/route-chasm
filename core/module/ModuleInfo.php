<?php

namespace core\module;

readonly class ModuleInfo {
    public function __construct(
        public string $identifier,
        public string $version
    ) {}
}