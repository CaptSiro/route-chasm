<?php

namespace core\pages;

use components\core\Admin\Nexus\DefaultLinkCreator;
use core\App;
use core\route\Path;
use core\url\Url;

class PageLinkCreator extends DefaultLinkCreator {
    public function getCreateUrl(Path $path): Url {
        $request = App::getInstance()->getRequest();
        $url = $request->getUrl()->copy();
        $url->setPath($path);
        return $url;
    }
}