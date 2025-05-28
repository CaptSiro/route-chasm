<?php

namespace core\route\compiler;

use core\route\Route;
use core\route\RouteSegment;
use core\utils\Regex;

class RouteCompiler {
    public function __construct(
        protected RouteCompilerOptions $options = new RouteCompilerOptions()
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

        /** @var array<Token> $tokens
         */
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
                        throw new RouteCompilerException("Illegal token '$literal'");
                    }

                    $ident = $tokens[$position + 1]->literal;
                    $identRegex = Regex::create($this->options->getIdentRegex());

                    if (!preg_match($identRegex, $ident)) {
                        throw new RouteCompilerException("'$ident' is not valid parameter name");
                    }

                    $regex = $parameters[$ident] ?? $this->options->getAnyRegex();
                    $segment .= Regex::createNamedGroup($ident, $regex);
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
                        if ($this->options->isMergeConsecutiveSlashes()) {
                            break;
                        }

                        throw new RouteCompilerException("Illegal token '$literal'. Cannot parse consecutive slashes");
                    }

                    if (!$this->valid($segment)) {
                        break;
                    }

                    $route->add(new RouteSegment($segment));
                    $segment = "";
                    break;
                }

                case TokenType::ANY: {
                    $segment .= $this->options->getAnyRegex();
                    break;
                }

                case TokenType::ANY_TERMINATOR: {
                    $segment .= $this->options->getAnyRegex();

                    $routeSegment = new RouteSegment($segment, $this->options->getAnyTerminatorWeight());
                    $routeSegment->setFlag(RouteSegment::FLAG_IS_TERMINAL);
                    $route->add($routeSegment);
                    break 2;
                }

                case TokenType::BRACKET_R: throw new RouteCompilerException("Unexpected token '$literal'");
                case TokenType::ILLEGAL: throw new RouteCompilerException("Illegal token '$literal'");
                case TokenType::EOF: {
                    if (!$this->valid($segment)) {
                        break 2;
                    }

                    $route->add(new RouteSegment($segment));
                    break 2;
                }

            }
        }

        return $route;
    }
}