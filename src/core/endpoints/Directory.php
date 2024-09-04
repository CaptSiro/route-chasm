<?php

namespace core\endpoints;

use components\core\Explorer\Explorer;
use components\core\HttpError\HttpError;
use core\Flags;
use core\http\Cors;
use core\http\HttpCode;
use core\http\HttpHeader;
use core\http\HttpMethod;
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
                    Cors::ORIGIN => "*"
                ]);

                $response->flush();
                break;
            }

            case HttpMethod::GET: {
                $remaining = urldecode($request->param->get(Request::PARAM_ANY_TERMINATOR, ""));
                $path = realpath($this->directory .'/'. $remaining);

                if ($path === false) {
                    $response->render(new HttpError("File not found", HttpCode::CE_NOT_FOUND));
                    break;
                }

                if (!str_contains($path, $this->directory)) {
                    $response->render(new HttpError(
                        "Request references outside of given scope",
                        HttpCode::CE_BAD_REQUEST
                    ));
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

                    $response->render(new HttpError(
                        "Request references directory",
                        HttpCode::CE_BAD_REQUEST
                    ));
                    break;
                }

                $response->setHeaders([
                    Cors::ORIGIN => "*",
                    HttpHeader::CONTENT_TYPE => Files::mimeType($path)
                ]);

                if (Files::extension($path) === "php" && $request->url->query->exists("x")) {
                    $response->generateHeaders();
                    require $path;
                    $response->flush();
                }

                $response->readFile($path);
            }

            default: {
                $response->render(new HttpError(
                    "HTTP method $request->httpMethod is not allowed",
                    HttpCode::CE_METHOD_NOT_ALLOWED
                ));
                break;
            }
        }
    }
}