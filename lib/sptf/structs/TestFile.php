<?php

namespace sptf\structs;

class TestFile {
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
}