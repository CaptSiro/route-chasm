<?php

namespace core;

use components\core\Message\Message;
use components\core\Resource\Index;
use components\core\Resource\Read;
use core\database\Table;
use core\http\Http;
use core\path\Path;
use core\url\UrlBuilder;
use InvalidArgumentException;
use patterns\Number;
use patterns\Pattern;

abstract class Resource {
    use Source;



    public const PARAM_UNIQUE = "unique";

    public const URL_INDEX = "index";
    public const URL_CREATE = "create";
    public const URL_READ = "read";
    public const URL_UPDATE = "update";
    public const URL_DELETE = "delete";



    protected Router $router;

    public function __construct() {
        $this->router = new Router();

        $this->router->use(
            "/",
            Http::get(fn(Request $request, Response $response) => $response->render($this->index())),
            Http::post(fn(Request $request, Response $response) => $response->render($this->create($request)))
        );

        $this->router->use(
            Path::from("/[unique]")
                ->param("unique", $this->getUniquePattern()),

            fn(Request $request) => $request->set("model", $this->fromUnique($request->param->getStrict(self::PARAM_UNIQUE))),

            Http::get(fn(Request $request, Response $response) => $response->render($this->read($request->get("model")))),
            Http::put(fn(Request $request, Response $response) => $response->render($this->update($request->get("model")))),
            Http::delete(fn(Request $request, Response $response) => $response->render($this->delete($request->get("model")))),
        );
    }



    abstract protected function getTable(): string;

    protected function fromUnique(string $unique): Table {
        return call_user_func($this->getTable() ."::fromUnique", $unique);
    }

    public function getUniquePattern(): Pattern {
        return Number::getInstance();
    }

    public function getRouter(): Router {
        return $this->router;
    }

    public function getUrl(?string $type = null): UrlBuilder {
        $path = App::getInstance()
            ->prependHome($this->router->getUrlPath());

        return new UrlBuilder(path: match ($type) {
            null,
            self::URL_INDEX,
            self::URL_CREATE => $path,

            self::URL_UPDATE,
            self::URL_DELETE,
            self::URL_READ => $this->appendUniqueIdent($path),
        });
    }

    private function appendUniqueIdent($path): string {
        if (str_ends_with($path, "/")) {
            return $path ."[". self::PARAM_UNIQUE .']';
        }

        return $path ."/[". self::PARAM_UNIQUE .']';
    }

    public function index(?array $models = null): Render {
        $models ??= call_user_func($this->getTable() ."::fetchAll");

        $responseType = App::getInstance()
            ->getRequest()
            ->getResponseType();
        if ($responseType === Response::TYPE_JSON) {
            return new JsonComponent($models);
        }

        $class = $this->getClass();
        $index = new Index("$class", $models ?? call_user_func($this->getTable() ."::fetchAll"));
        $index->setTemplate($this->getSource("$class.index.phtml"));
        return $index;
    }

    public function create(Request $request): Render {
        $model = new ($this->getTable());

        if (!($model instanceof Table)) {
            $class = $this->getTable();
            throw new InvalidArgumentException("The provided table class '$class' is not descendant of class ". Table::class);
        }

        $model
            ->setDictionary($request->body)
            ->save();

        return new Message("Created");
    }


    public function read(Table $model): Render {
        $request = App::getInstance()->getRequest();
        $type = $request->getResponseType();

        if ($type === Response::TYPE_JSON) {
            return new JsonComponent($model);
        }

        $class = $this->getClass();
        $read = new Read("$class - ". $request->param->get(self::PARAM_UNIQUE), $model);
        $read->setTemplate($this->getSource("$class.read.phtml"));
        return $read;
    }

    public function update(Table $model): Render {
        $model
            ->setDictionary(App::getInstance()->getRequest()->body)
            ->save();

        return new Message("Updated");
    }

    public function delete(Table $model): Render {
        $model->delete();

        return new Message("Deleted");
    }
}