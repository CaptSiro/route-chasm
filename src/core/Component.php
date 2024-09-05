<?php

namespace core;

use core\endpoints\Endpoint;
use core\endpoints\SimpleEndpoint;
use core\utils\Files;

class Component implements Render, Endpoint {
    use SimpleEndpoint;
    use Source;



    function render(?string $template = null): string {
        $file = $template ?? $this->getSource(basename(get_class($this)) .".phtml");

        if (Files::extension($file) === null) {
            $file .= ".phtml";
        }

        if (!file_exists($file)) {
            throw new DoesNotExistException("Could not locate template '$file'", $file);
        }

        ob_start();
        require $file;
        return ob_get_clean();
    }

    public function __toString(): string {
        return $this->render();
    }

    public function isMiddleware(): bool {
        return false;
    }

    function execute(Request $request, Response $response): void {
        $response->render($this);
    }
}