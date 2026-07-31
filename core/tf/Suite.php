<?php

namespace core\tf;

use core\view\renderers\XmlRenderer;
use core\view\ViewTemplateTrait;
use JsonSerializable;

class Suite implements JsonSerializable {
    use ViewTemplateTrait;



    protected int $passed;
    protected int $failed;
    protected array $failedAssertions;

    /**
     * @param string $name
     * @param float $time
     * @param array<Assertion> $assertions
     * @param SuiteOutput $output
     */
    public function __construct(
        protected string $name,
        protected float $time,
        protected array $assertions,
        protected SuiteOutput $output
    ) {
        $this->passed = 0;
        $this->failed = 0;

        foreach ($this->assertions as $assertion) {
            if (!$assertion->result()) {
                $this->failed++;
                continue;
            }

            $this->passed++;
        }

        $this->failedAssertions = array_filter(
            $this->assertions,
            fn(Assertion $x) => !$x->result()
        );
    }



    public function getName(): string {
        return $this->name;
    }

    public function getTime(): float {
        return $this->time;
    }

    public function getAssertions(): array {
        return $this->assertions;
    }

    public function getFailed(): int {
        return $this->failed;
    }

    public function getPassed(): int {
        return $this->passed;
    }

    public function getOutput(): SuiteOutput {
        return $this->output;
    }

    public function getOutcome(): TestOutcome {
        return TestOutcome::fromStats($this->passed, $this->failed);
    }

    public function toXml(): string {
        return $this->renderTemplated(
            $this->getTemplate(XmlRenderer::TEMPLATE_EXTENSION)
        );
    }



    // JsonSerializable
    public function jsonSerialize(): array {
        return [
            'name' => $this->name,
            'outcome' => $this->getOutcome(),
            'time' => $this->time,
            'failed' => $this->failed,
            'passed' => $this->passed,
            'assertions' => array_map(
                fn(Assertion $x) => $x->error(),
                $this->failedAssertions
            )
        ];
    }
}