<?php

namespace core\pages;

use components\core\Admin\Nexus\DefaultLinkCreator;
use core\App;
use core\route\Path;

class PageLinkCreator extends DefaultLinkCreator {
    public function getCreateLink(string $path): string {
        $request = App::getInstance()->getRequest();
        $url = $request->getUrl()->copy();
        $url->setPath(Path::from($path));
        return $url;
    }
}