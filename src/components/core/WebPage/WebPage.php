<?php

namespace components\core\WebPage;

use components\core\HtmlHead\HtmlHead;
use core\AdminRouter;
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
        $env = App::getInstance()->getEnv();

        $this->language ??= $env?->get("WEB_LANGUAGE") ?? "en";
        $this->head ??= new HtmlHead();

        $this->setTemplate(
            $this->getSource('WebPage.phtml')
        );
    }



    public function render(?string $template = null): string {
        if (AdminRouter::isAdmin(App::getInstance()->getRequest())) {
            $source = $this->getSource('WebPage.phtml');

            if ($this->template === $source) {
                $this->setTemplate($this->getSource('WebPage.admin.phtml'));
            }
        }

        return parent::render();
    }

    /**
     * @return Head
     */
    public function getHead(): Head {
        return $this->head;
    }
}