<?php

namespace core\actions\Assets\policy;

use core\actions\Assets\Assets;
use core\App;
use core\http\HttpCode;

class NotAccessiblePolicy implements DirectoryPolicy {
    public function __construct(
        protected int $code = HttpCode::CE_FORBIDDEN
    ) {}

    public function handle(Assets $assets, string $path): void {
        App::getInstance()
            ->getResponse()
            ->sendMessage(
                "Resource is not accessible",
                $this->code
            );
    }
}