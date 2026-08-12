<?php

namespace models\extensions\IsDefault;

use components\nexus\Nexus;
use components\nexus\NexusExtension;
use core\communication\Request;
use core\communication\Response;
use core\database\sql\Model;
use core\database\sql\ModelDescription;
use core\http\HttpCode;
use core\http\HttpHeader;
use core\ResourceLoader;
use core\route\Router;
use core\url\Url;

class IsDefaultExtension implements NexusExtension {
    use ResourceLoader;

    public const QUERY_MODEL_ID = 'default-model-id';



    protected Nexus $context;

    public function __construct(
        protected ModelDescription $modelDescription
    ) {}



    public function createSetAsDefaultUrl(Model $model): ?Url {
        if (!isset($this->context)) {
            return null;
        }

        $ret = $this->context->getUrl();
        $ret->getPath()
            ->append('set-as-default');

        $ret->setQueryArgument(self::QUERY_MODEL_ID, $model->getId());
        return $ret;
    }

    public function onBind(Nexus $context, Router $router): void {
        $this->context = $context;

        $router->use('/set-as-default', function (Request $request, Response $response) {
            $id = $request->getUrl()
                ->getQuery()
                ->get(self::QUERY_MODEL_ID);

            $model = $this->modelDescription
                ->getFactory()
                ->fromId($id);

            if (!($model instanceof IsDefault)) {
                $response->sendStatus(HttpCode::CE_BAD_REQUEST);
            }

            $model->setAsDefault();
            $response->setHeader(HttpHeader::X_RELOAD, 'Reload');
            $response->sendStatus(HttpCode::S_OK);
        });
    }
}