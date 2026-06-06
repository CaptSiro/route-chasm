<?php

namespace core\actions\Assets\servers;

use core\communication\Request;
use core\communication\Response;
use core\http\Cors;
use core\http\HttpHeader;
use core\utils\Files;

class FileServer implements Server {
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