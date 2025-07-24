<?php

namespace core\view;

use components\core\WebPage\ContextAwareWebPage;
use core\communication\Request;
use core\communication\Response;

class ContainerContent extends Component {
    protected Container $container;

    public static function getDefaultContainer(): Container {
        return new ContextAwareWebPage();
    }



    public function __construct(?Container $container = null) {
        parent::__construct();

        $this->container = $container ?? self::getDefaultContainer();
        $this->container->addContent($this);
    }



    public function getRoot(): View {
        return $this->container;
    }

    public function perform(Request $request, Response $response): void {
        $response->renderRoot($this);
    }
}