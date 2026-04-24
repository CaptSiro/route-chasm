<?php

namespace components\core\HtmlHead;

use components\core\Search\Search;
use components\core\WebPage\Head;
use core\App;
use core\fs\FileSystem;
use core\RouteChasmEnvironment;
use core\view\Component;
use core\view\StringRenderer;
use core\view\View;
use project\Startuh;

class HtmlHead extends Component implements Head {
    protected array $meta;
    protected array $elements;



    public function __construct(
        protected string $title = "",
    ) {
        parent::__construct();

        $this->meta = [];
        $this->elements = [];

        $env = App::getInstance()->getEnv();
        if (!is_null($env)) {
            $this->addMeta("author", $env->get(RouteChasmEnvironment::ENV_PROJECT_AUTHOR));
        }

        $this->addElement(new StringRenderer(FileSystem::createApi()));
        $this->addElement(new StringRenderer(Search::createApi()));

        $this->addElement(new StringRenderer(Startuh::createApi()));
    }



    public function addMeta(string $name, ?string $content): self {
        if (is_null($content)) {
            return $this;
        }

        $this->meta[$name] = $content;
        return $this;
    }

    public function addMetaNonEmpty(string $name, ?string $content): self {
        if (empty($content)) {
            return $this;
        }

        return $this->addMeta($name, $content);
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

    public function addElement(View $view): static {
        $this->elements[] = $view;
        return $this;
    }
}