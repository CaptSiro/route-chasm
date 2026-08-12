<?php

namespace core\actions\Assets\policy;

use components\Explorer;
use core\actions\Assets\Assets;
use core\App;

class ShowExplorerPolicy implements DirectoryPolicy {
    public function handle(Assets $assets, string $path): void {
        $app = App::getInstance();

        $remaining = urldecode($app->getRequest()->getAnyParam() ?? '');

        $app->getResponse()
            ->render(new Explorer(
                $path,
                basename($assets->getDirectories()[0]) .'/'. $remaining, // todo fix for multiple asset directories
                $app->getRequest()->getUrl()->getPath()->toString(),
                $assets->getDirectories()[0] !== $path
            ));
    }
}