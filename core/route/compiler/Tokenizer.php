<?php

namespace core\route\compiler;

use Generator;

class Tokenizer {
    public const EOF = Token::LITERAL_EOF;

    private int $position;
    private string $char;
    private int $length;



    public function __construct(
        private readonly string $input
    ) {
        $this->position = -1;
        $this->char = self::EOF;
        $this->length = strlen($this->input);
    }



    protected function peek(int $offset = 1): string {
        $position = $this->position + $offset;
        return $position < $this->length
            ? $this->input[$position]
            : self::EOF;
    }

    protected function readChar(): string {
        $this->position++;
        if ($this->position >= $this->length) {
            return $this->char = self::EOF;
        }

        return $this->char = $this->input[$this->position];
    }

    protected function readChunk(int $length): string {
        if ($length <= 0) {
            return "";
        }

        if ($length === 1) {
            return $this->char;
        }

        $chunk = substr($this->input, $this->position, $length);
        $this->position += $length - 1;

        return $chunk;
    }

    protected function readIdent(): string {
        $offset = 1;

        while (true) {
            $char = $this->peek($offset);

            if ($char === self::EOF
                || $char === "["
                || $char === "]"
                || $char === "/"
                || $char === "*"
            ) {
                return $this->readChunk($offset);
            }

            $offset++;
        }
    }

    protected function readAny(): Token {
        if ($this->peek() === "*") {
            return new Token(TokenType::ANY_TERMINATOR, "**");
        }

        return new Token(TokenType::ANY, "*");
    }

    /**
     * @return Generator<Token>
     */
    public function tokenize(): Generator {
        while (true) {
            $char = $this->readChar();

            $token = match ($char) {
                "\0" => Token::eof(),
                "*" => $this->readAny(),
                "/" => new Token(TokenType::SLASH, "/"),
                "[" => new Token(TokenType::BRACKET_L, "["),
                "]" => new Token(TokenType::BRACKET_R, "]"),
                default => new Token(TokenType::IDENT, $this->readIdent())
            };

            yield $token;

            if ($token->isTerminal()) {
                return;
            }
        }
    }
}