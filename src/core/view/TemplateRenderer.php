<?php

namespace core\view;

use core\DoesNotExistException;
use core\ResourceLoader;
use core\utils\Files;

trait TemplateRenderer {
    use ResourceLoader;

    protected ?string $template = null;



    public function renderTemplated(?string $template = null): string {
        $file = $this->template ?? $template ?? $this->getResource($this->getClass() .".phtml");

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

    public function setTemplate(?string $template): static {
        $this->template = $template;
        return $this;
    }

    public function __toString(): string {
        return $this->render();
    }
}