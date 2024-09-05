<?php

namespace components\core\WebPage;

use components\core\HtmlHead\HtmlHead;
use core\App;
use core\Component;
use core\Render;
use core\Singleton;

class WebPage extends Component {
    public function __construct(
        protected ?string $language = null,
        protected ?Head $head = null,
        protected ?Render $content = null
    ) {
        $env = App::getInstance()->getEnv();

        $this->language ??= $env?->get("WEB_LANGUAGE") ?? "en";
        $this->head ??= new HtmlHead();
    }



    public function render(?string $template = null): string {
        return parent::render(__DIR__ . "/WebPage.phtml");
    }

    /**
     * @return Head
     */
    public function getHead(): Head {
        return $this->head;
    }

    /**
     * @return Render
     */
    public function getContent(): Render {
        return $this->content;
    }

    /**
     * @param Render $content
     */
    public function setContent(Render $content): void {
        $this->content = $content;
    }
}