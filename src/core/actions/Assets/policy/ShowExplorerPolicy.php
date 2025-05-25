<?php

namespace core\actions\Assets\policy;

use components\core\Explorer\Explorer;
use core\actions\Assets\Assets;
use core\App;

class ShowExplorerPolicy implements DirectoryPolicy {
    public function handle(Assets $directory, string $path): void {
        $app = App::getInstance();

        // todo
        $remaining = urldecode($app->getRequest()->getAnyParam() ?? '');

        $app->getResponse()
            ->renderRoot(new Explorer(
                $path,
                basename($directory->getDirectory()) .'/'. $remaining,
                $app->getRequest()->getUrl()->getRealPath(),
                $directory->getDirectory() !== $path
            ));
    }
}