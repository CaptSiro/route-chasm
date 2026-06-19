<?php

namespace components\fs;

use components\forms\controls\Select\Select;
use components\forms\Form;
use core\actions\Action;
use core\actions\ActionBindRouteNode;
use core\actions\ActorClassName;
use core\communication\Format;
use core\communication\Request;
use core\communication\Response;
use core\fs\FileSystem;
use core\fs\variants\FileVariantTransformer;
use core\route\RouteNode;
use core\view\FormatAble;
use core\view\FormatAbleTrait;
use core\view\Formatter;
use core\view\View;
use core\view\ViewTemplate;
use JsonSerializable;

class FileVariantTransformers implements ViewTemplate, Action, FormatAble {
    use ActionBindRouteNode, ActorClassName, FormatAbleTrait;



    protected Formatter $formatter;

    /**
     * @param array<FileVariantTransformer> $transformers
     */
    public function __construct(
        protected array $transformers,
        protected string $selectName = self::class,
        protected string $selectLabel = 'Transformers'
    ) {
        $this->setFormatter(Formatter::default($this));
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



    // FormatAble
    public function toHtml(): string {
        $values = [];
        foreach ($this->transformers as $transformer) {
            $values[FileSystem::createVariantIdentifier($transformer)] = $transformer->getTransformerLabel();
        }

        Form::importAssets();
        return new Select($this->selectName, $this->selectLabel, $values);
    }

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