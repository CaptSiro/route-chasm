<?php

namespace components\pages;

use components\nexus\DefaultNexusUrlCreator;
use core\App;
use core\route\Path;
use core\url\Url;

class PageNexusUrlCreator extends DefaultNexusUrlCreator {
    public function getCreateUrl(Path $path): Url {
        $request = App::getInstance()->getRequest();
        $url = $request->getUrl()->copy();
        $url->setPath($path);
        return $url;
    }
}