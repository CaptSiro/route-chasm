<?php

namespace core\view;

use components\core\WebPage\ContextAwareWebPage;

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
}