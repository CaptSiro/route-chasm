<?php

namespace core\view2;

use core\utils\Objects;
use core\view2\renderers\HtmlRenderer;
use core\view2\renderers\XmlRenderer;
use JsonSerializable;

class PageView extends Component implements JsonSerializable {
    public static function from(View $view, string $title): self {
        $payload = new DataTransfer($view);
        $payload[Head::PROPERTY_TITLE] = $title;

        return new self($view, $payload);
    }

    public static function fromComponent(Component $component): self {
        return new self($component, $component);
    }



    protected Head $head;

    public function __construct(
        protected View $view,
        protected Payload $payload,
        Renderer $renderer = new HtmlRenderer(),
    ) {
        parent::__construct($renderer);

        $this[XmlRenderer::TEMPLATE] = self::getSelfResource(Objects::getBaseClass(self::class) . '.xml.php');
        $this->head = new Head($this->payload, $this->renderer);
    }



    public function getHead(): Head {
        return $this->head;
    }



    // Component
    public function setRenderer(Renderer $renderer): static {
        $this->renderer = $renderer;

        if ($this->view instanceof Component) {
            $this->view->setRenderer($renderer);
        }

        $this->head->setRenderer($renderer);
        return $this;
    }

    public function jsonSerialize(): array {
        $ret = ['head' => $this->head];

        if ($this->view instanceof JsonSerializable) {
            $ret['body'] = $this->view;
        }

        return $ret;
    }
}