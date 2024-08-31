<?php

namespace core\path\parser;

use Generator;

class Tokenizer {
    private int $position;
    private string $char;
    private int $inputLen;



    public function __construct(
        private readonly string $input
    ) {
        $this->position = 0;
        $this->char = "\0";
        $this->inputLen = strlen($this->input);
    }



    private function readChar(): void {
        if ($this->position >= $this->inputLen) {
            $this->char = "\0";
        } else {
            $this->char = $this->input[$this->position];
        }

        $this->position++;
    }

    private function peek(int $offset = 0): string {
        $pos = $this->position + $offset;
        return $pos < $this->inputLen
            ? $this->input[$pos]
            : "\0";
    }

    private function ident(): string {
        $literal = $this->char;

        while (true) {
            $char = $this->peek();

            if ($char === "\0"
                || $char === "["
                || $char === "]"
                || $char === "/"
                || $char === "*") {
                return $literal;
            }

            $literal .= $char;

            $this->readChar();
        }
    }

    public function tokenize(): Generator {
        while (true) {
            $this->readChar();

            $token = match ($this->char) {
                "\0" => new Token(TokenType::EOF, "\0"),
                "*" => $this->any(),
                "/" => new Token(TokenType::SLASH, "/"),
                "[" => new Token(TokenType::BRACKET_L, "["),
                "]" => new Token(TokenType::BRACKET_R, "]"),
                default => new Token(TokenType::IDENT, $this->ident())
            };

            yield $token;

            if ($token->type === TokenType::EOF
                || $token->type === TokenType::ILLEGAL) {
                return;
            }
        }
    }

    protected function any(): Token {
        if ($this->peek() === "*") {
            return new Token(TokenType::ANY_TERMINATOR, "**");
        }

        return new Token(TokenType::ANY, "*");
    }
}