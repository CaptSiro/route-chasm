<?php

namespace core\path\parser;

readonly class Token {
    public function __construct(
        public TokenType $type,
        public string $literal
    ) {}
}