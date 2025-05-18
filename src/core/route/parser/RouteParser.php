<?php

namespace core\route\parser;

use core\route\Route;

class RouteParser {
    public const REGEX_ANY = '.*';
    public const REGEX_ALPHA = '[a-zA-Z]';
    public const REGEX_ALPHANUMERIC = '[a-zA-Z0-9]';
    public const REGEX_IDENT = self::REGEX_ALPHA . self::REGEX_ALPHANUMERIC . '*';

    public static function createParameter(string $name, string $regex): string {
        return "(<$name>$regex)";
    }



    public function __construct(
        protected string $identRegex = self::REGEX_IDENT,
        protected string $anyRegex = self::REGEX_ANY,
        protected bool $mergeConsecutiveSlashes = true,
    ) {}



    protected function valid(string $segment): bool {
        return strlen($segment) !== 0;
    }

    /**
     * @param string $pattern
     * @param array<string, string> $parameters Identifier => REGEX
     * @return Route
     */
    public function parse(string $pattern, array $parameters = []): Route {
        $route = new Route();
        $segment = '';

        /** @var Token[] $tokens */
        $tokens = [...(new Tokenizer($pattern))->tokenize()];
        $count = count($tokens);

        for ($position = 0; $position < $count; $position++) {
            $literal = $tokens[$position]->literal;

            switch ($tokens[$position]->type) {
                case TokenType::BRACKET_L: {
                    $identAndClosingBracketFollows = ($position + 2 < $count)
                        && $tokens[$position + 1]->type === TokenType::IDENT
                        && $tokens[$position + 2]->type === TokenType::BRACKET_R;

                    if (!$identAndClosingBracketFollows) {
                        throw new RouteParsingException("Illegal token '$literal'");
                    }

                    $ident = $tokens[$position + 1]->literal;

                    if (!preg_match("/$this->identRegex/", $ident)) {
                        throw new RouteParsingException("'$ident' is not valid parameter name");
                    }

                    $regex = $parameters[$ident] ?? $this->anyRegex;
                    $segment .= self::createParameter($ident, $regex);
                    $position += 2;
                    break;
                }

                case TokenType::IDENT: {
                    $segment .= $literal;
                    break;
                }

                case TokenType::SLASH: {
                    $isPreviousSlash = isset($tokens[$position - 1])
                        && $tokens[$position - 1]->type === TokenType::SLASH;
                    if ($isPreviousSlash) {
                        if ($this->mergeConsecutiveSlashes) {
                            break;
                        }

                        throw new RouteParsingException("Illegal token '$literal'. Cannot parse consecutive slashes");
                    }

                    if (!$this->valid($segment)) {
                        break;
                    }

                    $route->add($segment);
                    $segment = "";
                    break;
                }

                case TokenType::ANY: {
                    $segment .= $this->anyRegex;
                    break;
                }

                case TokenType::ANY_TERMINATOR: {
                    $segment .= $this->anyRegex;
                    $route->add($segment);
                    break 2;
                }

                case TokenType::BRACKET_R: throw new RouteParsingException("Unexpected token '$literal'");
                case TokenType::ILLEGAL: throw new RouteParsingException("Illegal token '$literal'");
                case TokenType::EOF: {
                    if (!$this->valid($segment)) {
                        break 2;
                    }

                    $route->add($segment);
                    break 2;
                }

            }
        }

        return $route;
    }
}