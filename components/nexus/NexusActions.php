<?php

namespace components\nexus;

use Closure;
use core\actions\Action;
use core\communication\Request;
use core\communication\Response;
use core\database\sql\ModelDescription;
use core\http\HttpCode;

class NexusActions {
    public static function none(): self {
        return new self(null, null, null);
    }

    public static function fromEditor(
        ModelDescription $modelDescription,
        NexusEditor $editor,
    ): self {
        $factory = $modelDescription->getFactory();

        return new self(
            $editor,

            function (Request $request, Response $response) use ($editor, $factory) {
                $id = $request->getParam()->get('id');
                $model = $factory->fromId($id);

                if (is_null($model)) {
                    $response->sendStatus(HttpCode::CE_NOT_FOUND);
                }

                return $editor->setModel($model);
            },

            function (Request $request, Response $response) use ($factory) {
                $model = $factory->fromId(
                    $request->getParam()->get('id')
                );

                if (is_null($model)) {
                    $response->sendStatus(HttpCode::CE_NOT_FOUND);
                }

                $model->delete();
                $response->sendStatus(HttpCode::S_OK);
            },
        );
    }

    public static function fromBehavior(
        ModelDescription $modelDescription,
        NexusEditorBehavior $behavior,
    ): self {
        return self::fromEditor(
            $modelDescription,
            new NexusEditor($modelDescription, $behavior),
        );
    }



    public function __construct(
        protected Action|Closure|null $create = null,
        protected Action|Closure|null $update = null,
        protected Action|Closure|null $delete = null,
    ) {}



    public function getCreate(): Action|Closure|null {
        return $this->create;
    }

    public function getDelete(): Action|Closure|null {
        return $this->delete;
    }

    public function getUpdate(): Action|Closure|null {
        return $this->update;
    }
}