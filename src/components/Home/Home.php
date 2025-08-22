<?php

namespace components\Home;

use components\core\WebPage\ContextAwareWebPage;

class Home extends ContextAwareWebPage {
    public function __construct() {
        parent::__construct();
        $this->setTemplate($this->getResource("Home"));
    }
}