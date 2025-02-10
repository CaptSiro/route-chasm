<?php

namespace core\endpoints;

use Closure;
use components\core\Explorer\Explorer;
use core\App;
use core\communication\Request;
use core\communication\Response;
use core\Flags;
use core\http\Cors;
use core\http\HttpCode;
use core\http\HttpHeader;
use core\http\HttpMethod;
use core\utils\Files;

class Directory implements Endpoint {
    use SimpleEndpoint;
    use Flags;

    public const FLAG_LIST_DIRECTORIES = 0b1;

    public static function showExplorer(): Closure {
        return function (self $directory, string $path) {
            $app = App::getInstance();
            $remaining = urldecode($app->getRequest()->getParam()->get(Request::PARAM_ANY_TERMINATOR, ""));

            $app->getResponse()
                ->render(new Explorer(
                    $path,
                    basename($directory->getDirectory()) .'/'. $remaining,
                    $app->getRequest()->getUrl()->getRealPath(),
                    $directory->getDirectory() !== $path
                ));
        };
    }

    /**
     * @param string[] $defaults
     * @return Closure
     */
    public static function showDefaultFile(array $defaults = ['index.html']): Closure {
        return function (self $directory, string $path) use ($defaults) {
            foreach ($defaults as $file) {
                if (!file_exists($path .'/'. $file)) {
                    continue;
                }

                $app = App::getInstance();
                $directory->serveFile($path .'/'. $file, $app->getRequest(), $app->getResponse());
            }

            App::getInstance()
                ->getResponse()
                ->error(
                    "Resource is not accessible",
                    HttpCode::CE_FORBIDDEN
                );
        };
    }

    public static function notAccessible(int $code = HttpCode::CE_FORBIDDEN): Closure {
        return function () use ($code) {
            App::getInstance()
                ->getResponse()
                ->error(
                    "Resource is not accessible",
                    $code
                );
        };
    }

    private Closure $onDirectory;



    public function __construct(
        protected string $directory
    ) {
        $this->directory = realpath($this->directory);
    }



    public function getDirectory(): string {
        return $this->directory;
    }

    public function onDirectory(Closure $onDirectory): self {
        $this->onDirectory = $onDirectory;
        return $this;
    }

    public function isMiddleware(): bool {
        return false;
    }

    public function execute(Request $request, Response $response): void {
        switch ($request->httpMethod) {
            case HttpMethod::OPTIONS: {
                $response->setHeaders([
                    Cors::METHODS => "GET",
                    Cors::HEADERS => strtolower(HttpHeader::ACCESS_CONTROL_ALLOW_ORIGIN),
                    Cors::CREDENTIALS => "true",
                    Cors::ORIGIN => "*",
                ]);

                $response->flush();
                break;
            }

            case HttpMethod::GET: {
                $remaining = urldecode($request->getParam()->get(Request::PARAM_ANY_TERMINATOR, ""));
                $path = realpath($this->directory .'/'. $remaining);

                if ($path === false) {
                    $response->error(
                        "File not found",
                        HttpCode::CE_NOT_FOUND
                    );
                    break;
                }

                if (!str_contains($path, $this->directory)) {
                    $response->error(
                        "Request references outside of given scope",
                        HttpCode::CE_BAD_REQUEST
                    );
                    break;
                }

                if (is_dir($path)) {
                    if (!is_null($this->onDirectory)) {
                        ($this->onDirectory)($this, $path);
                    }

                    $response->error(
                        "Resource is not accessible",
                        HttpCode::CE_FORBIDDEN
                    );

                    break;
                }

                $this->serveFile($path, $request, $response);
            }

            default: {
                $response->error(
                    "HTTP method $request->httpMethod is not allowed",
                    HttpCode::CE_METHOD_NOT_ALLOWED
                );
                break;
            }
        }
    }

    public function serveFile(string $path, Request $request, Response $response): void {
        $response->setHeaders([
            Cors::ORIGIN => "*",
            HttpHeader::CONTENT_TYPE => Files::mimeType($path),
        ]);

        if (Files::extension($path) === "php" && $request->getUrl()->getQuery()->exists("x")) {
            $response->generateHeaders();
            require $path;
            $response->flush();
        }

        $response->readFile($path);
    }
}