<?php

namespace core\actions\Assets\policy;

use components\core\Explorer\Explorer;
use core\actions\Assets\Assets;
use core\App;

class ShowExplorerPolicy implements DirectoryPolicy {
    public function handle(Assets $assets, string $path): void {
        $app = App::getInstance();

        $remaining = urldecode($app->getRequest()->getAnyParam() ?? '');

        $app->getResponse()
            ->renderRoot(new Explorer(
                $path,
                basename($assets->getDirectory()) .'/'. $remaining,
                $app->getRequest()->getUrl()->getPath()->toString(),
                $assets->getDirectory() !== $path
            ));
    }
}