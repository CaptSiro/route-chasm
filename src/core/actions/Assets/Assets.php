<?php

namespace core\actions\Assets;

use core\actions\Assets\servers\FileServer;
use core\actions\Assets\servers\Server;
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

class Assets extends Controller {
    use Flags;



    protected DirectoryPolicy $directoryPolicy;
    protected Server $server;

    public function __construct(
        protected string $directory
    ) {
        parent::__construct();
        $this->directory = realpath($this->directory);
        $this->directoryPolicy = new NotAccessiblePolicy();
        $this->server = new FileServer();
    }



    public function getDirectory(): string {
        return $this->directory;
    }

    public function setDirectoryPolicy(DirectoryPolicy $directoryPolicy): static {
        $this->directoryPolicy = $directoryPolicy;
        return $this;
    }

    public function getServer(): Server {
        return $this->server;
    }

    public function setServer(Server $server): void {
        $this->server = $server;
    }

    public function perform(Request $request, Response $response): void {
        switch ($request->getHttpMethod()) {
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
                $remaining = $request->getRemainingPath()->toString();
                $path = realpath($this->directory .'/'. $remaining);

                if ($path === false) {
                    $response->sendMessage(
                        "File not found",
                        HttpCode::CE_NOT_FOUND
                    );
                    break;
                }

                if (!str_starts_with($path, $this->directory)) {
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

                $this->server->serve($path, $request, $response);
            }

            default: {
                $httpMethod = $request->getHttpMethod();
                $response->sendMessage(
                    "HTTP method $httpMethod is not allowed",
                    HttpCode::CE_METHOD_NOT_ALLOWED
                );
                break;
            }
        }
    }
}