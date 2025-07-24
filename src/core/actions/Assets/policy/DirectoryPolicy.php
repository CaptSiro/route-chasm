<?php

namespace core\actions\Assets\policy;

use core\actions\Assets\Assets;

interface DirectoryPolicy {
    public function handle(Assets $assets, string $path): void;
}