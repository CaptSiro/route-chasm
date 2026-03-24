<?php

namespace core;

use Closure;
use core\collections\dictionary\Map;
use core\collections\dictionary\StrictMap;
use core\communication\FormatMatcher;
use core\communication\parser\FormBodyParser;
use core\communication\parser\JsonBodyParser;
use core\communication\parser\RequestBody;
use core\communication\parser\RequestBodyParser;
use core\communication\parser\TextBodyParser;
use core\communication\Request;
use core\communication\RequestFormat;
use core\communication\Response;
use core\communication\ResponseFormat;
use core\configs\AppConfig;
use core\configs\Config;
use core\http\HttpCode;
use core\locale\Locale;
use core\module\Loader;
use core\module\Module;
use core\route\compiler\RouteCompiler;
use core\route\Path;
use core\route\Router;
use core\url\Url;
use dotenv\Env;
use models\core\Language\Language;
use models\core\ModuleRecord;
use ReflectionClass;

class App implements Loader {
    private static ?self $instance = null;

    public static function getInstance(): self {
        if (is_null(self::$instance)) {
            self::$instance = new self();
            self::$instance->requireDefaultModules();
            self::$instance->loadLocales();
        }

        return self::$instance;
    }



    /**
     * @see App
     */
    public const EVENT_SHUTDOWN = self::class .':shutdown';



    public const DATABASE = "app";

    public const KEY_LOGGED_IN_USER = 'user';

    public const OPTION_ALWAYS_RETURN_HTML_FOR_HTTP_GET = "always_return_html_for_http_get";
    public const OPTION_DO_NOT_AUTOLOAD = 'do_not_autoload';



    /**
     * @return array<Module>
     */
    protected static function getDefaultModules(): array {
        return [];
    }

    public static function getDefaultLanguage(): Language {
        return Language::getDefault()
            ?? Language::fromEnv();
    }

    public static function createOptionDoNotAutoload(string $moduleClass): string {
        return self::OPTION_DO_NOT_AUTOLOAD ."_$moduleClass";
    }



    private Router $router;
    private Request $request;
    private Response $response;
    private string $src;
    private FormatMatcher $matcher;
    private readonly Map $options;
    /**
     * @var RequestBodyParser[]
     */
    private array $bodyParsers;
    protected ?Env $env;
    protected array $listeners;
    protected bool $defaultModulesLoaded;
    protected array $modules;
    protected array $locales = [];
    protected array $loaded = [];
    protected ?string $home;



    public function __construct() {
        $this->defaultModulesLoaded = false;

        $this->src = realpath(__DIR__ . "/..");
        $this->options = new Map([
            App::OPTION_ALWAYS_RETURN_HTML_FOR_HTTP_GET => true,
        ]);

        $this->router = new Router();
        $this->matcher = new FormatMatcher();
        $this->initCommunication();

        $this->env = self::getEnvStatic();

        $this->home = null;

        $this->bodyParsers = [
            JsonBodyParser::getInstance(),
            FormBodyParser::getInstance(),
            TextBodyParser::getInstance()
        ];
    }



    private function initCommunication(): void {
        $this->request = new Request(
            $this,
            (new RequestFormat())->setFormatMatcher($this->matcher),
            Url::fromRequest(),
            new StrictMap(),
        );

        $this->response = new Response(
            (new ResponseFormat())->setFormatMatcher($this->matcher),
        );
    }

    public function setMatcher(FormatMatcher $matcher): void {
        $this->matcher = $matcher;
        $this->initCommunication();
    }

    public function addBodyParser(RequestBodyParser $parser): void {
        $this->bodyParsers[] = $parser;
    }

    public function parseBody(Request $request): RequestBody {
        foreach ($this->bodyParsers as $parser) {
            if ($parser->supports($request->getFormat())) {
                return $parser->parse($request);
            }
        }

        $this->response->sendMessage(
            "Request body could not be parsed. Format '" .$request->getFormat(). "' is not supported.",
            HttpCode::SE_INTERNAL_SERVER_ERROR
        );

        exit;
    }

    /**
     * @return array<string, Locale>
     */
    public function getLocales(): array {
        return $this->locales;
    }

    public function addLocale(Locale $locale): void {
        $this->locales[] = $locale;
    }

    protected function loadLocales(): void {
        foreach (glob(__DIR__. '/../modules/locales/*.php') as $locale) {
            [$class, $_] = explode('.', basename($locale), 2);
            $reflection = new ReflectionClass("\\modules\\locales\\$class");

            if ($reflection->isSubclassOf(Locale::class) && !$reflection->isAbstract()) {
                /** @var Locale $instance */
                $instance = $reflection->newInstance();
                $this->locales[$instance->getIdentifier()] = $instance;
            }
        }
    }

    public function getOptions(): Map {
        return $this->options;
    }

    public function getMainRouter(): Router {
        return $this->router;
    }

    public function getRequest(): Request {
        return $this->request;
    }

    public function getResponse(): Response {
        return $this->response;
    }

    public function getProjectName(): ?string {
        return $this->env->get(RouteChasmEnvironment::ENV_PROJECT);
    }

    public function getProjectAuthor(): ?string {
        return $this->env->get(RouteChasmEnvironment::ENV_PROJECT_AUTHOR);
    }

    public function getProjectAuthorLink(): ?string {
        return $this->env->get(RouteChasmEnvironment::ENV_PROJECT_AUTHOR_LINK);
    }

    public function getSource(string $resource): string {
        return "$this->src/$resource";
    }

    public function getHome(): string {
        if (!is_null($this->home)) {
            return $this->home;
        }

        $home = "";
        $dir = dirname($_SERVER["SCRIPT_FILENAME"]);

        for ($i = 0; $i < strlen($dir); $i++) {
            if (!(isset($_SERVER["DOCUMENT_ROOT"][$i]) && $_SERVER["DOCUMENT_ROOT"][$i] == $dir[$i])){
                $home .= $dir[$i];
            }
        }

        $this->home = $home;
        return $home;
    }

    public function attach(Path $relative): Path {
        return $this->getRequest()
            ->getDomain()
            ->attach($relative);
    }

    public static function getEnvStatic(): ?Env {
        return file_exists(RouteChasmEnvironment::FILE_ENV)
            ? Env::fromFile(RouteChasmEnvironment::FILE_ENV)
            : null;
    }

    public function getEnv(): ?Env {
        return $this->env;
    }

    public static function getConfig(): ?Config {
        return AppConfig::getInstance()
            ->get();
    }

    public function getRouteCompiler(): RouteCompiler {
        return AppConfig::getInstance()
            ->get()
            ->getRouteCompiler();
    }

    /**
     * @return array<Module>
     */
    public function getLoadedModules(): array {
        return $this->loaded;
    }

    public function require(Module $module): self {
        if (!isset($this->modules)) {
            $modules = ModuleRecord::all();
            $this->modules = [];

            foreach ($modules as $moduleModel) {
                $this->modules[$moduleModel->identifier] = $moduleModel;
            }
        }

        $info = $module->getInfo();
        $migrateFrom = null;
        $doSaveVersion = false;

        if (!isset($this->modules[$info->identifier])) {
            $this->modules[$info->identifier] = ModuleRecord::createFromInfo($info);
            $migrateFrom = '';
        } else {
            $definition = $this->modules[$info->identifier];
            if ($definition->version !== $info->version) {
                $migrateFrom = $definition->version;
                $doSaveVersion = true;
            }
        }

        if (!is_null($migrateFrom)) {
            $module->migrate($migrateFrom);

            if ($doSaveVersion) {
                $definition = $this->modules[$info->identifier];
                $definition->version = $info->version;
                $definition->save();
            }
        }

        $module->load($this);
        $this->loaded[] = $module;
        return $this;
    }

    protected function requireDefaultModules(): void {
        if ($this->defaultModulesLoaded) {
            return;
        }

        $this->defaultModulesLoaded = true;

        foreach (self::getDefaultModules() as $module) {
            if ($this->options->exists(self::createOptionDoNotAutoload(get_class($module)))) {
                continue;
            }

            $this->require($module);
        }
    }

    public function serve(?Request $request = null, ?Response $response = null): void {
        $request ??= $this->request;

        $path = Path::from(
            $request->getUrl()->getPath(),
            $request->getDomain()->getPath()->getDepth()
        );

        $this->router->performActions(
            $path,
            $request,
            $response ?? $this->response
        );
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