<?php

namespace core;

use Closure;
use core\dictionary\Map;
use core\dictionary\StrictMap;
use Exception;

class App {
    use Singleton;



    public const OPTION_DO_REMOVE_HOME_FROM_URL_PATH = "do_remove_home_from_url_path";
    public const OPTION_ALWAYS_RETURN_HTML_FOR_HTTP_GET = "always_return_html_for_http_get";



    private Router $router;
    private Request $request;
    private Response $response;
    private string $src;
    private Closure $responseTypeMatcher;
    public readonly Map $options;



    public function __construct() {
        $this->src = realpath(__DIR__ . "/..");
        $this->options = new Map([
            App::OPTION_DO_REMOVE_HOME_FROM_URL_PATH => false,
            App::OPTION_ALWAYS_RETURN_HTML_FOR_HTTP_GET => true,
        ]);

        $this->router = new Router();

        $this->responseTypeMatcher = fn(string $type) => match ($type) {
            'j', 'json' => 'JSON',
            'h', 'html' => 'HTML',
            default => 'TEXT'
        };

        $this->request = new Request(
            $this,
            Url::fromRequest(),
            new StrictMap(),
            new StrictMap(),
            new StrictMap(),
            new StrictMap()
        );

        $this->response = new Response();
    }



    /**
     * @return Closure
     */
    public function getResponseTypeMatcher(): Closure {
        return $this->responseTypeMatcher;
    }

    /**
     * @param Closure $responseTypeMatcher
     */
    public function setResponseTypeMatcher(Closure $responseTypeMatcher): void {
        $this->responseTypeMatcher = $responseTypeMatcher;
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

    /**
     * @return Response
     */
    public function getResponse(): Response {
        return $this->response;
    }

    public function getSource(string $resource): string {
        return "$this->src/$resource";
    }

    public function getHome(): string {
        $home = "";
        $dir = dirname($_SERVER["SCRIPT_FILENAME"]);

        for ($i = 0; $i < strlen($dir); $i++) {
            if (!(isset($_SERVER["DOCUMENT_ROOT"][$i]) && $_SERVER["DOCUMENT_ROOT"][$i] == $dir[$i])){
                $home .= $dir[$i];
            }
        }

        return $home;
    }

    public function serve(?Request $request = null): void {
        $req = $request ?? $this->request;
        $this->router->call($req, $this->response);
    }
}