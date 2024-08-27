<?php

namespace core;

use Closure;
use core\dictionary\StrictMap;
use Exception;

class App {
    use Singleton;



    public static function notDefinedCallback(): Closure {
        return fn($reason) => throw new Exception($reason);
    }

    public static function noTemplateFile(): Closure {
        return fn($file) => throw new Exception($file);
    }




    private Router $router;
    private Request $request;
    private string $src;



    private function __construct() {
        $this->router = new Router();
        $this->src = realpath(__DIR__ . "/..");
        $this->request = new Request(
            Url::fromRequest(),
            new StrictMap(self::notDefinedCallback(), []),
            new StrictMap(self::notDefinedCallback(), []),
            new StrictMap(self::notDefinedCallback(), []),
            new StrictMap(self::notDefinedCallback(), [])
        );
    }



    /**
     * @return Router
     */
    public function getMainRouter(): Router {
        return $this->router;
    }

    /**
     * @return Request
     */
    public function getRequest(): Request {
        return $this->request;
    }

    public function getSource(string $resource): string {
        return "$this->src/$resource";
    }

    public function serve(?Request $request = null): void {

    }
}