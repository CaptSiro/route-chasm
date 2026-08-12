<?php

namespace core\view;

use core\view\renderers\HtmlRenderer;

class Component implements ViewTemplate, Payload {
    use ViewTemplateTrait, PayloadTrait;

    public static function propagateSetRenderer(mixed $variable, Renderer $renderer): void {
        if ($variable instanceof Component) {
            $variable->setRenderer($renderer);
        }
    }



    protected ?string $title = null;

    public function __construct(
        protected ?Renderer $renderer = null
    ) {
        if (is_null($this->renderer)) {
            $this->renderer = HtmlRenderer::getInstance();
        }
    }



    public function getTitle(): ?string {
        return $this->title;
    }

    public function setTitle(string $title): static {
        $this->title = $title;
        $this->setProperty(Head::PAYLOAD_TITLE, $title);
        return $this;
    }

    public function addPropertyHtmlMeta(string $key, string $meta): static {
        $this->addProperty(Head::PAYLOAD_HTML_META, $meta, $key);
        return $this;
    }

    /**
     * @param array<string|View> $elements
     * @return $this
     */
    public function addPropertyHtmlElements(array $elements): static {
        $this->addAllProperties(Head::PAYLOAD_HTML_ELEMENTS, $elements);
        return $this;
    }

    public function setRenderer(Renderer $renderer): static {
        $this->renderer = $renderer;
        return $this;
    }

    public function render(): string {
        return $this->renderer->render($this);
    }

    public function __toString(): string {
        return $this->render();
    }
}