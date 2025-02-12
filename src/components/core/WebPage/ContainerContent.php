<?php

namespace components\core\WebPage;

use components\core\HtmlHead\HtmlHead;
use core\communication\Request;
use core\communication\Response;
use core\view\Component;
use core\view\Container;
use core\view\Render;

class ContainerContent extends Component {
    protected Container $container;

    public static function getDefaultContainer(): Container {
        return new WebPage();
    }



    public function __construct(?Container $container = null) {
        $this->container = $container ?? self::getDefaultContainer();
        $this->container->addContent($this);
    }



    public function getRoot(): Render {
        return $this->container;
    }

    public function execute(Request $request, Response $response): void {
        $response->renderRoot($this);
    }
}