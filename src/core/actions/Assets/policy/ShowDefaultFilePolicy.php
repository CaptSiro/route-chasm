<?php

namespace core\actions\Assets\policy;

use core\actions\Assets\Assets;
use core\App;
use core\http\HttpCode;

class ShowDefaultFilePolicy implements DirectoryPolicy {
    public function __construct(
        protected array $defaultFiles = ['index.html']
    ) {}

    public function handle(Assets $assets, string $path): void {
        foreach ($this->defaultFiles as $file) {
            if (!file_exists($path .'/'. $file)) {
                continue;
            }

            $app = App::getInstance();
            $assets->getServer()->serve($path .'/'. $file, $app->getRequest(), $app->getResponse());
        }

        App::getInstance()
            ->getResponse()
            ->sendMessage(
                "Resource is not accessible",
                HttpCode::CE_FORBIDDEN
            );
    }
}