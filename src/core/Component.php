<?php

namespace core;

use core\endpoints\Endpoint;
use core\endpoints\SimpleEndpoint;
use core\utils\Files;

class Component implements Render, Endpoint {
    use SimpleEndpoint;
    use Source;



    function render(?string $template = null): string {
        $file = $template ?? basename(get_class($this));
        if (Files::extension($file) === null) {
            $file .= ".phtml";
        }

        $source = $this->getSource($file);

        if (!file_exists($source)) {
            (App::noTemplateFile())($source);
        }

        ob_start();
        require $source;
        return ob_get_clean();
    }

    public function __toString(): string {
        return $this->render();
    }

    function call(Request $request): void {
        // todo Response.render($this)
    }
}