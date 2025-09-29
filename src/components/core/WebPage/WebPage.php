<?php

namespace components\core\WebPage;

use components\core\HtmlHead\HtmlHead;
use core\App;
use core\view\ArrayContainer;
use core\view\Component;
use core\view\Container;

class WebPage extends Component implements Container {
    use ArrayContainer;



    public function __construct(
        protected ?string $language = null,
        protected ?Head $head = null,
    ) {
        parent::__construct();
        $env = App::getInstance()->getEnv();

        // todo
        // get from request... (preference, domain, ...)
        $this->language ??= App::getInstance()->getRequest()->getLanguage()->code;
        $this->head ??= new HtmlHead();

        $this->setTemplate(
            $this->getResource('WebPage.phtml')
        );
    }



    public function getHead(): Head {
        return $this->head;
    }
}