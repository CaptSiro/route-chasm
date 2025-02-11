<?php

namespace core\view;

use core\DoesNotExistException;
use core\Source;
use core\utils\Files;

trait TemplateRenderer {
    use Source;

    protected ?string $template = null;



    public function render(?string $template = null): string {
        $file = $this->template ?? $template ?? $this->getSource(basename(get_class($this)) .".phtml");

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

    public function setTemplate(?string $template): self {
        $this->template = $template;
        return $this;
    }

    public function __toString(): string {
        return $this->render();
    }
}