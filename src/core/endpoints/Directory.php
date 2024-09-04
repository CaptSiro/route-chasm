<?php

namespace core\endpoints;

use components\core\Explorer\Explorer;
use components\core\HttpError\HttpError;
use core\Flags;
use core\Http;
use core\Request;
use core\Response;
use core\utils\Files;

class Directory implements Endpoint {
    use SimpleEndpoint;
    use Flags;

    public const FLAG_LIST_DIRECTORIES = 0b1;



    public function __construct(
        protected string $directory
    ) {
        $this->directory = realpath($this->directory);
    }



    public function call(Request $request, Response $response): void {
        switch ($request->httpMethod) {
            case Http::METHOD_OPTIONS: {
                $response->setHeaders([
                    Http::CORS_METHODS => "GET",
                    Http::CORS_HEADERS => strtolower(Http::HEADER_ACCESS_CONTROL_ALLOW_ORIGIN),
                    Http::CORS_CREDENTIALS => "true",
                    Http::CORS_ORIGIN => "*"
                ]);
                $response->flush();
                break;
            }

            case Http::METHOD_GET: {
                $path = realpath($this->directory .'/'. urldecode($request->param->get(Request::PARAM_ANY_TERMINATOR, "")));

                if ($path === false) {
                    $response->render(new HttpError("File not found", Response::CODE_NOT_FOUND));
                    break;
                }

                if (!str_contains($path, $this->directory)) {
                    $response->render(new HttpError("Request references outside of given scope", Response::CODE_BAD_REQUEST));
                    break;
                }

                if (is_dir($path)) {
                    if ($this->hasFlag(self::FLAG_LIST_DIRECTORIES)) {
                        $response->render(new Explorer(
                            $path,
                            $request->url->getRealPath(),
                            $this->directory !== $path
                        ));
                    }

                    $response->render(new HttpError("Request references directory", Response::CODE_BAD_REQUEST));
                    break;
                }

                $response->setHeaders([
                    Http::CORS_ORIGIN => "*",
                    Http::HEADER_CONTENT_TYPE => Files::mimeType($path)
                ]);

                if (Files::extension($path) === "php" && $request->url->query->exists("x")) {
                    $response->generateHeaders();
                    require $path;
                    $response->flush();
                }

                $response->readFile($path);
            }

            default: {
                $response->render(new HttpError("HTTP method $request->httpMethod is not allowed", Response::CODE_METHOD_NOT_ALLOWED));
                break;
            }
        }
    }
}