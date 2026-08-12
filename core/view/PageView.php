<?php

namespace core\view;

use core\actions\Action;
use core\actions\UserResourceBarrier;
use core\communication\Request;
use core\communication\Response;
use core\route\RouteNode;
use core\utils\Objects;
use core\view\renderers\XmlRenderer;
use JsonSerializable;
use models\Language\Language;
use models\UserResource;
use RuntimeException;

class PageView extends Controller implements JsonSerializable {
    public static function from(View $view, string $title): static {
        $payload = new DataTransfer($view);
        $payload->setProperty(Head::PAYLOAD_TITLE, $title);

        $ret = new static();

        $ret
            ->setView($view)
            ->setPayload($payload);

        return $ret;
    }

    public static function fromComponent(Component $component): static {
        $ret = new static();

        return $ret
            ->setView($component)
            ->setPayload($component);
    }



    protected Head $head;
    protected View $view;
    protected Payload $payload;

    public function __construct(
        ?View $view = null,
        ?Payload $payload = null,
        ?Renderer $renderer = null,
    ) {
        parent::__construct($renderer);

        $this->setProperty(XmlRenderer::TEMPLATE, self::getSelfResource(Objects::getBaseClass(self::class) . '.xml.php'));

        if (!is_null($view)) {
            $this->setView($view);
        }

        if (!is_null($payload)) {
            $this->setPayload($payload);
        }
    }



    public function getHead(?Language $language = null): Head {
        if (!isset($this->head)) {
            $this->head = new Head($this->getPayload(), $this->renderer);
        }

        if (!is_null($language)) {
            $this->head->setLanguage($language);
        }

        return $this->head;
    }

    public function getView(): View {
        if (!isset($this->view)) {
            throw new RuntimeException('View is not set');
        }

        return $this->view;
    }

    public function setView(View $view): static {
        $this->view = $view;

        if ($this->view instanceof Action && isset($this->routeNode)) {
            $this->view->onBind($this->routeNode);
        }

        return $this;
    }

    public function getPayload(): Payload {
        if (!isset($this->payload)) {
            throw new RuntimeException('Payload is not set');
        }

        return $this->payload;
    }

    public function setPayload(Payload $payload): static {
        $this->payload = $payload;
        $this->head = new Head($payload, $this->renderer);
        return $this;
    }

    public function setComponent(Component $component): static {
        return $this
            ->setView($component)
            ->setPayload($component);
    }



    // Controller
    public function setUserResource(?UserResource $userResource = null): static {
        if ($this->view instanceof UserResourceBarrier) {
            $this->view->setUserResource($userResource);
        }

        return parent::setUserResource($userResource);
    }

    public function performControllerAction(Request $request, Response $response): void {
        if ($this->view instanceof Action) {
            $this->view->perform($request, $response);
        }

        $response->render($this);
    }

    public function onBind(RouteNode $bindingPoint): void {
        parent::onBind($bindingPoint);

        if ($this->view instanceof Action) {
            $this->view->onBind($bindingPoint);
        }
    }

    public function setRenderer(Renderer $renderer): static {
        $this->renderer = $renderer;

        Component::propagateSetRenderer($this->view, $renderer);
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