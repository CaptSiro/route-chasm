<?php

namespace core\view;

use core\utils\Objects;

trait FormatAbleTrait {
    use Renderer;



    protected Formatter $formatter;



    public function setFormatter(Formatter $formatter): self {
        $this->formatter = $formatter;
        return $this;
    }

    public function getFormatter(): Formatter {
        return $this->formatter;
    }



    // View
    public function renderFormatter(): string {
        if (!isset($this->formatter)) {
            // todo dev warning
            return $this->toHtml();
        }

        return $this->formatter->render();
    }

    public function render(): string {
        return $this->renderFormatter();
    }



    // FormatAble
    public function toText(): string {
        return Objects::getClass($this);
    }

    public function toHtml(): string {
        return $this->renderTemplated();
    }

    public function toJson(): string {
        return json_encode($this);
    }

    public function toXml(): string {
        $xml = $this->getTemplate(".xml.php");
        if (!file_exists($xml)) {
            // todo dev warning
            return '';
        }

        return $this->renderTemplated($xml);
    }
}