<?php

namespace components\core\HtmlHead;

use components\core\WebPage\Head;
use core\App;
use core\Component;

class HtmlHead extends Component implements Head {
    protected array $meta;



    public function __construct(
        protected string $title = "",
    ) {
        $this->meta = [];

        $env = App::getInstance()->getEnv();
        if (!is_null($env)) {
            $this->addMeta("author", $env->get("WEB_AUTHOR"));
        }
    }



    public function addMeta(string $name, ?string $content): self {
        if (is_null($content)) {
            return $this;
        }

        $this->meta[$name] = $content;
        return $this;
    }

    public function setTitle(string $title): void {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getTitle(): string {
        return $this->title;
    }
}