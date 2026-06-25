<?php

namespace components\html;

use components\Search\Search;
use components\layout\WebPage\Head;
use core\App;
use core\fs\FileSystem;
use core\RouteChasmEnvironment;
use core\view\Component;
use core\view\StringRenderer;
use core\view\View;
use models\Language\Language;

class HtmlHead extends Component implements Head {
    protected array $meta;
    protected array $elements;
    protected Language $language;



    public function __construct(
        protected string $title = "",
    ) {
        parent::__construct();

        $this->meta = [];
        $this->elements = [];
        $this->language = App::getInstance()
            ->getRequest()
            ->getLanguage();

        $env = App::getInstance()->getEnv();
        if (!is_null($env)) {
            $this->addMeta("author", $env->get(RouteChasmEnvironment::ENV_PROJECT_AUTHOR));
        }

        $this->addElement(new StringRenderer(FileSystem::createApi()));
        $this->addElement(new StringRenderer(Search::createApi()));
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

    public function addElement(View|string $view): static {
        $this->elements[] = $view;
        return $this;
    }

    public function setLanguage(Language $language): static {
        $this->language = $language;
        return $this;
    }

    public function getLanguageCode(): string {
        return $this->language->code;
    }
}