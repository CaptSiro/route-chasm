<?php

namespace core\tf;

use core\view\renderers\XmlRenderer;
use core\view\ViewTemplateTrait;
use JsonSerializable;

class TestFile implements JsonSerializable {
    use ViewTemplateTrait;



    protected int $failed;
    protected int $passed;

    /**
     * @param string $file
     * @param array<Suite> $suites
     */
    public function __construct(
        protected string $file,
        protected array $suites
    ) {
        $this->failed = 0;
        $this->passed = 0;

        foreach ($this->suites as $suite) {
            $this->failed += $suite->getFailed();
            $this->passed += $suite->getPassed();
        }
    }



    public function getFile(): string {
        return $this->file;
    }

    public function getPassed(): int {
        return $this->passed;
    }

    public function getFailed(): int {
        return $this->failed;
    }

    public function getSuites(): array {
        return $this->suites;
    }

    public function toXml(): string {
        return $this->renderTemplated(
            $this->getTemplate(XmlRenderer::TEMPLATE_EXTENSION)
        );
    }



    // JsonSerializable
    public function jsonSerialize(): array {
        return [
            'file' => $this->file,
            'failed' => $this->failed,
            'passed' => $this->passed,
            'suits' => $this->suites,
        ];
    }
}