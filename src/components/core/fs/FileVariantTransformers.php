<?php

namespace components\core\fs;

use core\actions\Action;
use core\actions\ActionBindRouteNode;
use core\actions\ActorClassName;
use core\communication\Format;
use core\communication\Request;
use core\communication\Response;
use core\forms\controls\Select\Select;
use core\forms\Form;
use core\fs\FileSystem;
use core\fs\variants\FileVariantTransformer;
use core\route\RouteNode;
use core\view\Formatter;
use core\view\View;
use JsonSerializable;

class FileVariantTransformers implements View, Action, JsonSerializable {
    use ActionBindRouteNode, ActorClassName;



    protected Formatter $formatter;

    /**
     * @param array<FileVariantTransformer> $transformers
     */
    public function __construct(
        protected array $transformers,
        protected string $selectName = self::class,
        protected string $selectLabel = 'Transformers'
    ) {
        $this->formatter = new Formatter(fn($type) => match ($type) {
            Format::IDENT_HTML => $this->renderSelect(),
            default => json_encode($this)
        });
    }



    // View
    public function render(): string {
        return $this->formatter->render();
    }

    protected function renderSelect(): View {
        $values = [];
        foreach ($this->transformers as $transformer) {
            $values[FileSystem::createVariantIdentifier($transformer)] = $transformer->getTransformerLabel();
        }

        Form::importAssets();
        return new Select($this->selectName, $this->selectLabel, $values);
    }

    public function getRoot(): View {
        return $this;
    }

    public function __toString(): string {
        return $this->render();
    }



    // Action
    public function isMiddleware(): bool {
        return false;
    }

    public function onBind(RouteNode $bindingPoint): void {
        $this->bindRouteNode($bindingPoint);
    }

    public function perform(Request $request, Response $response): void {
        $response->renderRoot($this);
    }



    // JsonSerializable
    public function jsonSerialize(): array {
        $json = [];

        foreach ($this->transformers as $transformer) {
            $json[] = [
                'identifier' => FileSystem::createVariantIdentifier($transformer),
                'label' => $transformer->getTransformerLabel()
            ];
        }

        return $json;
    }
}