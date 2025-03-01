<?php

namespace sptf\structs;

use sptf\interfaces\Assertion;
use sptf\TestOutcome;

class Suite {
    protected int $passed;
    protected int $failed;

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
}