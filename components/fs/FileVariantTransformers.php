<?php

namespace components\fs;

use components\forms\controls\Select;
use components\forms\Form;
use core\actions\Action;
use core\actions\ActionBindRouteNode;
use core\actions\ActorClassName;
use core\communication\Request;
use core\communication\Response;
use core\fs\FileSystem;
use core\fs\variants\FileVariantTransformer;
use core\route\RouteNode;
use core\view\Component;
use core\view\IncorrectRendererOverrideCallException;
use core\view\Payload;
use core\view\Renderer;
use core\view\RendererOverride;
use core\view\renderers\HtmlRenderer;

class FileVariantTransformers extends Component implements Action, RendererOverride {
    use ActionBindRouteNode, ActorClassName;



    /**
     * @param array<FileVariantTransformer> $transformers
     */
    public function __construct(
        protected array $transformers,
        protected string $selectName = self::class,
        protected string $selectLabel = 'Transformers',
        ?Renderer $renderer = new HtmlRenderer()
    ) {
        parent::__construct($renderer);
    }



    // Action
    public function isMiddleware(): bool {
        return false;
    }

    public function onBind(RouteNode $bindingPoint): void {
        $this->bindRouteNode($bindingPoint);
    }

    public function perform(Request $request, Response $response): void {
        $response->render($this);
    }



    // Component
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



    // RendererOverride
    public function hasRendererOverride(Renderer $renderer, Payload $payload): bool {
        return $renderer instanceof HtmlRenderer;
    }

    public function performRendererOverride(Renderer $renderer, Payload $payload): string {
        if (!$this->hasRendererOverride($renderer, $payload)) {
            throw new IncorrectRendererOverrideCallException(HtmlRenderer::class, $renderer::class);
        }

        return $this->toHtml();
    }

    public function toHtml(): string {
        $values = [];
        foreach ($this->transformers as $transformer) {
            $values[FileSystem::createVariantIdentifier($transformer)] = $transformer->getTransformerLabel();
        }

        Form::importAssets();
        return new Select($this->selectName, $this->selectLabel, $values);
    }
}