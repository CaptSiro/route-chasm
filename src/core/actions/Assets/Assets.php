<?php

namespace core\actions\Assets;

use core\actions\Controller;
use core\actions\Assets\policy\DirectoryPolicy;
use core\actions\Assets\policy\NotAccessiblePolicy;
use core\communication\Request;
use core\communication\Response;
use core\Flags;
use core\http\Cors;
use core\http\HttpCode;
use core\http\HttpHeader;
use core\http\HttpMethod;
use core\utils\Files;

class Assets extends Controller {
    use Flags;



    protected DirectoryPolicy $directoryPolicy;

    public function __construct(
        protected string $directory
    ) {
        parent::__construct();
        $this->directory = realpath($this->directory);
        $this->directoryPolicy = new NotAccessiblePolicy();
    }



    public function getDirectory(): string {
        return $this->directory;
    }

    public function setDirectoryPolicy(DirectoryPolicy $directoryPolicy): void {
        $this->directoryPolicy = $directoryPolicy;
    }

    public function perform(Request $request, Response $response): void {
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
                // todo
                $remaining = urldecode($request->getAnyParam() ?? "");
                $path = realpath($this->directory .'/'. $remaining);

                if ($path === false) {
                    $response->sendMessage(
                        "File not found",
                        HttpCode::CE_NOT_FOUND
                    );
                    break;
                }

                if (!str_contains($path, $this->directory)) {
                    $response->sendMessage(
                        "Request references outside of given scope",
                        HttpCode::CE_BAD_REQUEST
                    );
                    break;
                }

                if (is_dir($path)) {
                    $this->directoryPolicy->handle($this, $path);
                    break;
                }

                $this->serve($path, $request, $response);
            }

            default: {
                $response->sendMessage(
                    "HTTP method $request->httpMethod is not allowed",
                    HttpCode::CE_METHOD_NOT_ALLOWED
                );
                break;
            }
        }
    }

    public function serve(string $path, Request $request, Response $response): void {
        $response->setHeaders([
            Cors::ORIGIN => "*",
            HttpHeader::CONTENT_TYPE => Files::mimeType($path),
        ]);

        // todo
        //                                               v   Add as web setting (default=false)   v
        if (Files::extension($path) === "php" && $request->getUrl()->getQuery()->exists("x")) {
            $response->generateHeaders();
            require $path;
            $response->flush();
        }

        $response->readFile($path);
    }
}