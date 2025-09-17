<?php

namespace components\core\Admin\Nexus;

use core\App;
use core\route\Path;
use core\Singleton;
use core\url\Url;

class DefaultLinkCreator implements NexusLinkCreator {
    use Singleton;



    protected Url $request;

    public function __construct() {
        $this->request = App::getInstance()->getRequest()->getUrl();
    }



    public function getCreateUrl(Path $path): Url {
        return $this->request->copy()
            ->setPath($path);
    }

    public function getUpdateUrl(Path $path, mixed $id): Url {
        return $this->request->copy()
            ->setPath($path);
    }

    public function getDeleteUrl(Path $path, mixed $id): Url {
        return $this->request->copy()
            ->setPath($path);
    }
}