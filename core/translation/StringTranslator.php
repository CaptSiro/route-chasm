<?php

namespace core\translation;

use Closure;

/**
 * @template-implements Translator<string>
 */
class StringTranslator implements Translator {
    protected array $sourceToTarget = [];
    protected array $targetToSource = [];



    public function __construct(
        protected Closure $function
    ) {}



    public function add(mixed $source): string {
        if (isset($this->sourceToTarget[$source])) {
            return $this->sourceToTarget[$source];
        }

        $target = ($this->function)($source);

        $this->sourceToTarget[$source] = $target;
        $this->targetToSource[$target] = $source;
        return $target;
    }

    public function getSource(mixed $target): ?string {
        return $this->targetToSource[$target] ?? null;
    }

    public function getTarget(mixed $source): ?string {
        return $this->sourceToTarget[$source] ?? null;
    }
}