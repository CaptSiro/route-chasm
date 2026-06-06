<?php

namespace core\route\compiler;

readonly class Token {
    public const LITERAL_EOF = "\0";

    public static function eof(): Token {
        return new Token(TokenType::EOF, self::LITERAL_EOF);
    }



    public function __construct(
        protected TokenType $type,
        protected string $literal
    ) {}



    public function getType(): TokenType {
        return $this->type;
    }

    public function getLiteral(): string {
        return $this->literal;
    }

    public function isTerminal(): bool {
        return $this->type === TokenType::EOF
            || $this->type === TokenType::ILLEGAL
            || $this->type === TokenType::ANY_TERMINATOR;
    }

    public function __toString(): string {
        $type = $this->type->name;
        return "Token($type, '$this->literal')";
    }
}