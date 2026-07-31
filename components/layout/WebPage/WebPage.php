<?php

namespace components\layout\WebPage;

use core\App;
use core\view\ArrayContainer;
use core\view\Component;
use core\view\Container;
use core\view\View;

class WebPage extends Component implements Container {
    use ArrayContainer;

    public static function wrap(View|string ...$content): static {
        $instance = new static();

        return $instance->addAllContent($content);
    }



    public function __construct(
        protected ?string $language = null,
        protected ?Head $head = null,
    ) {
        parent::__construct();

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