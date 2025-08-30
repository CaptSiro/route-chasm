<?php

namespace components\core\Message;

use core\actions\Action;
use core\actions\ActionBindRouteNode;
use core\communication\Format;
use core\communication\Request;
use core\communication\Response;
use core\route\RouteNode;
use core\view\Formatter;
use core\view\Renderer;
use core\view\View;
use JsonSerializable;

class Message implements Action, View, JsonSerializable {
    use Renderer, ActionBindRouteNode;



    protected Formatter $formatter;

    public function __construct(
        protected string $message
    ) {
        $this->formatter = new Formatter(fn($type) => match ($type) {
            Format::IDENT_HTML => $this->renderTemplated(),
            Format::IDENT_XML => "<message>$this->message</message>",
            Format::IDENT_JSON => json_encode($this),
            default => $this->message
        });
    }



    public function jsonSerialize(): array {
        return ['message' => $this->message];
    }

    public function render(): string {
        return $this->formatter->render();
    }



    // Action
    public function isMiddleware(): bool {
        return false;
    }

    public function getActorName(): string {
        if (strlen($this->message <= 16)) {
            return "Message($this->message)";
        }

        $sub = substr($this->message, 0, 16);
        return "Message($sub...)";
    }

    public function onBind(RouteNode $bindingPoint): void {
        $this->bindRouteNode($bindingPoint);
    }

    public function perform(Request $request, Response $response): void {
        $response->render($this);
    }
}