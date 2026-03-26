<?php

namespace models\extensions\Priority;

use Closure;
use components\core\Admin\Nexus\AdminNexus;
use components\core\Admin\Nexus\NexusExtension;
use core\communication\Request;
use core\communication\Response;
use core\database\sql\Model;
use core\database\sql\query\Query;
use core\database\sql\Sql;
use core\http\HttpCode;
use core\http\HttpHeader;
use core\ResourceLoader;
use core\route\Router;
use core\url\Url;

class PriorityExtension implements NexusExtension {
    use ResourceLoader;

    public const QUERY_MODEL_ID = 'p-model-id';
    public const QUERY_PRIORITY = 'p-priority';



    protected AdminNexus $context;

    /**
     * @param Closure|null $modifyUpdate `fn(UpdateQuery, Model) => void`
     */
    public function __construct(
        protected ?Closure $modifyUpdate = null
    ) {}



    public function createSetPriorityUrl(Model $model): ?Url {
        if (!isset($this->context)) {
            return null;
        }

        $ret = $this->context->getLink()
            ->copy();

        $ret->getPath()
            ->append('set-priority');

        $ret->setQueryArgument(self::QUERY_MODEL_ID, $model->getId());
        return $ret;
    }

    public function onBind(AdminNexus $context, Router $router): void {
        $this->context = $context;

        $router->use('/set-priority', function (Request $request, Response $response) {
            $query = $request->getUrl()
                ->getQuery();

            $description = $this->context->getModelDescription();
            $model = $description->getFactory()
                ->fromId($query->getStrict(self::QUERY_MODEL_ID));

            if (!($model instanceof Priority)) {
                $response->sendStatus(HttpCode::CE_BAD_REQUEST);
            }

            $target = intval($query->getStrict(self::QUERY_PRIORITY));
            $current = $model->getPriority();
            $update = null;
            $priority = COLUMN_PRIORITY;

            if ($current > $target) {
                $lower = $target;
                $upper = $current - 1;

                $update = Sql::update($description->getTable())
                    ->set($priority, "$priority + 1")
                    ->where(Query::infer("? <= $priority AND $priority <= ?", $lower, $upper));
            } else if ($target > $current) {
                $lower = $current + 1;
                $upper = $target;

                $update = Sql::update($description->getTable())
                    ->set($priority, "$priority - 1")
                    ->where(Query::infer("? <= $priority AND $priority <= ?", $lower, $upper));
            }

            if (!is_null($update)) {
                if (!is_null($this->modifyUpdate)) {
                    ($this->modifyUpdate)($update, $model);
                }

                $update->run($description->getConnection());
            }

            $model->setPriority($target, true);
            $response->setHeader(HttpHeader::X_RELOAD, 'Reload');
            $response->sendStatus(HttpCode::S_OK);
        });
    }
}