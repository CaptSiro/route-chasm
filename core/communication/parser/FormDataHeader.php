<?php

namespace core\communication\parser;

use JsonSerializable;

class FormDataHeader implements JsonSerializable {
    public static function from(string $string): self {
        $literals = [];
        $pairs = [];

        foreach (preg_split('/; ?/', $string) as $value) {
            $delimiter = strpos($value, '=');
            if ($delimiter === false) {
                $literals[] = $value;
                continue;
            }

            $name = substr($value, 0, $delimiter);
            $pairs[$name] = substr($value, $delimiter + 2, strlen($value) - $delimiter - 3);
        }

        return new self($literals, $pairs);
    }


    /**
     * @param array<string> $literals
     * @param array<string> $pairs
     */
    public function __construct(
        protected array $literals,
        protected array $pairs = []
    ) {}



    public function match(string $literal): bool {
        return in_array($literal, $this->literals);
    }

    public function getLiteral(): string {
        return implode('; ', $this->literals);
    }

    public function has(string $key): bool {
        return isset($this->pairs[$key]);
    }

    public function get(string $key): ?string {
        return $this->pairs[$key] ?? null;
    }

    public function jsonSerialize(): array {
        return [
            'literals' => $this->literals,
            'pairs' => $this->pairs
        ];
    }
}