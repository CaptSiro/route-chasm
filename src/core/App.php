<?php

namespace core;

use Closure;
use core\config\Config;
use core\dictionary\Map;
use core\dictionary\StrictMap;
use core\url\Url;
use dotenv\Env;

class App implements Loader {
    use Singleton;



    public const EVENT_SHUTDOWN = self::class .':shutdown';



    public const ENV = __DIR__ ."/../../.env";

    public const OPTION_DO_REMOVE_HOME_FROM_URL_PATH = "do_remove_home_from_url_path";
    public const OPTION_DO_ADD_HOME_TO_URL_PATH = "do_add_home_to_url_path";
    public const OPTION_ALWAYS_RETURN_HTML_FOR_HTTP_GET = "always_return_html_for_http_get";



    private Router $router;
    private Request $request;
    private Response $response;
    private string $src;
    private Closure $responseTypeMatcher;
    public readonly Map $options;
    protected ?Env $env;
    protected ?Config $config;
    protected array $listeners;



    public function __construct() {
        $this->src = realpath(__DIR__ . "/..");
        $this->options = new Map([
            App::OPTION_DO_REMOVE_HOME_FROM_URL_PATH => false,
            App::OPTION_DO_ADD_HOME_TO_URL_PATH => false,
            App::OPTION_ALWAYS_RETURN_HTML_FOR_HTTP_GET => true,
        ]);

        $this->router = new Router();

        $this->responseTypeMatcher = fn(string $type) => match ($type) {
            'j', 'json' => Response::TYPE_JSON,
            'h', 'html' => Response::TYPE_HTML,
            default => Response::TYPE_TEXT
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

        $this->env = file_exists(self::ENV)
            ? Env::fromFile(self::ENV)
            : null;

        $this->config = null;
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

    public function prependHome(string $path): string {
        $doAddHome = $this->options->get(self::OPTION_DO_ADD_HOME_TO_URL_PATH, false);

        if (!$doAddHome) {
            return $path;
        }

        $home = $this->getHome();

        if ($home === "") {
            return $path;
        }

        $prependSlash = !str_starts_with($path, '/');

        return ($prependSlash ? '/' : '') . $home . ($prependSlash ? '/' : '') . $path;
    }

    public function getEnv(): ?Env {
        return $this->env;
    }

    /**
     * @return Config|null
     */
    public function getConfig(): ?Config {
        return $this->config;
    }

    /**
     * @param Config $config
     */
    public function setConfig(Config $config): void {
        $this->config = $config;
    }

    public function require(Module $module): self {
        $module->load($this);
        return $this;
    }

    public function serve(?Request $request = null): void {
        $req = $request ?? $this->request;
        $this->router->execute($req, $this->response);
    }

    public function on(string $event, Closure $function): void {
        if (!isset($this->listeners[$event])) {
            $this->listeners[$event] = [$function];
            return;
        }

        $this->listeners[$event][] = $function;
    }

    public function dispatch(string $event, mixed $context): void {
        if (!isset($this->listeners[$event])) {
            return;
        }

        foreach ($this->listeners[$event] as $listener) {
            $listener($context);
        }
    }
}