<?php

namespace core;

use core\path\Path;
use patterns\Number;
use patterns\Pattern;

abstract class Resource {
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
            Http::get(fn() => $this->index()),
            Http::post(fn(Request $request) => $this->create($this->fromRequestData($request)))
        );

        $this->router->use(
            Path::from("/[unique]")
                ->param("unique", $this->getUniquePattern()),

            fn(Request $request) => $request->set("model", $this->fromUnique($request->param->getStrict("unique"))),

            Http::get(fn(Request $request) => $this->read($request->get("model"))),
            Http::put(fn(Request $request) => $this->update($request->get("model"))),
            Http::delete(fn(Request $request) => $this->delete($request->get("model"))),
        );

        return $this->router;
    }



    abstract protected function fromUnique(string $unique): Table;
    abstract protected function fromRequestData(Request $request): Table;

    public function getUniquePattern(): Pattern {
        return Number::getInstance();
    }

    public function getRouter(): Router {
        return $this->router;
    }

    public function getUrl(?string $type = null): string {
        $path = $this->router->getUrlPath();
        return match ($type) {
            null,
            self::URL_INDEX,
            self::URL_CREATE => $path,

            self::URL_UPDATE,
            self::URL_DELETE,
            self::URL_READ => $this->appendUniqueIdent($path),
        };
    }

    private function appendUniqueIdent($path): string {
        if (str_ends_with($path, "/")) {
            return $path ."[unique]";
        }

        return $path ."/[unique]";
    }

    public function index(): void {

    }

    public function create(Table $model): void {

    }

    public function read(Table $model): void {

    }

    public function update(Table $model): void {

    }

    public function delete(Table $model): void {

    }
}