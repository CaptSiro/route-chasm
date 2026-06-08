<?php

namespace core\actions\Assets;

use core\actions\Assets\policy\DirectoryPolicy;
use core\actions\Assets\policy\NotAccessiblePolicy;
use core\actions\Assets\servers\FileServer;
use core\actions\Assets\servers\Server;
use core\actions\Controller;
use core\communication\Request;
use core\communication\Response;
use core\Flags;
use core\http\Cors;
use core\http\HttpCode;
use core\http\HttpHeader;
use core\http\HttpMethod;
use core\route\Path;

class Assets extends Controller {
    use Flags;



    protected DirectoryPolicy $directoryPolicy;
    protected Server $server;

    /**
     * @param array<string> $directory
     */
    public function __construct(
        protected array $directory
    ) {
        parent::__construct();
        $this->directory = array_filter(
            array_map(fn($x) => realpath($x), $this->directory),
            fn($x) => is_string($x)
        );

        $this->directoryPolicy = new NotAccessiblePolicy();
        $this->server = new FileServer();
    }



    /**
     * @return array<string>
     */
    public function getDirectories(): array {
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

                foreach ($this->directory as $directory) {
                    if (!is_string($entry = realpath(Path::join($directory, $remaining)))) {
                        continue;
                    }

                    if (!str_starts_with($entry, $directory)) {
                        continue;
                    }

                    if (is_dir($entry)) {
                        $this->directoryPolicy->handle($this, $entry);
                        return;
                    }

                    $this->server->serve($entry, $request, $response);
                }

                $response->sendMessage(
                    "File not found",
                    HttpCode::CE_NOT_FOUND
                );

                return;
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