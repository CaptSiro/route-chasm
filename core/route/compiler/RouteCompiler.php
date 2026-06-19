<?php

namespace core\route\compiler;

use core\route\Path;
use core\route\Route;
use core\route\RouteSegment;
use core\utils\Regex;

class RouteCompiler {
    public function __construct(
        protected RouteCompilerConfig $config = new RouteCompilerConfig()
    ) {}



    protected function valid(string $segment): bool {
        return strlen($segment) !== 0;
    }

    public function isIdentValid(string $ident): bool {
        $identRegex = Regex::create($this->config->getIdentRegex());
        return preg_match($identRegex, $ident);
    }

    /**
     * @param string $pattern
     * @param array<string, string> $parameters Identifier => REGEX
     * @return Route
     */
    public function parse(string $pattern, array $parameters = []): Route {
        $route = new Route();
        $segment = '';
        $source = '';

        /** @var array<Token> $tokens */
        $tokens = [...(new Tokenizer($pattern))->tokenize()];
        $count = count($tokens);

        for ($position = 0; $position < $count; $position++) {
            $literal = $tokens[$position]->getLiteral();

            switch ($tokens[$position]->getType()) {
                case TokenType::BRACKET_L: {
                    $identAndClosingBracketFollows = ($position + 2 < $count)
                        && $tokens[$position + 1]->getType() === TokenType::IDENT
                        && $tokens[$position + 2]->getType() === TokenType::BRACKET_R;

                    if (!$identAndClosingBracketFollows) {
                        throw new RouteCompilerException("Illegal token '$literal'");
                    }

                    $ident = $tokens[$position + 1]->getLiteral();
                    $identRegex = Regex::create($this->config->getIdentRegex());

                    if (!preg_match($identRegex, $ident)) {
                        throw new RouteCompilerException("'$ident' is not valid parameter name");
                    }

                    $regex = $parameters[$ident] ?? $this->config->getAnyRegex();
                    $segment .= Regex::createNamedGroup($ident, $regex);
                    $source .= "[$ident]";
                    $position += 2;
                    break;
                }

                case TokenType::IDENT: {
                    $segment .= $literal;
                    $source .= $literal;
                    break;
                }

                case TokenType::SLASH: {
                    $isPreviousSlash = isset($tokens[$position - 1])
                        && $tokens[$position - 1]->getType() === TokenType::SLASH;
                    if ($isPreviousSlash) {
                        if ($this->config->isMergeConsecutiveSlashes()) {
                            break;
                        }

                        throw new RouteCompilerException("Illegal token '$literal'. Cannot parse consecutive slashes");
                    }

                    if (!$this->valid($segment)) {
                        break;
                    }

                    $route->add(new RouteSegment($source, $segment));
                    $segment = "";
                    $source = "";
                    break;
                }

                case TokenType::ANY: {
                    $segment .= $this->config->getAnyRegex();
                    $source .= '*';
                    break;
                }

                case TokenType::ANY_TERMINATOR: {
                    $segment .= $this->config->getAnyRegex();
                    $source .= '**';

                    $routeSegment = new RouteSegment($source, $segment);
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

                    $route->add(new RouteSegment($source, $segment));
                    break 2;
                }
            }
        }

        return $route;
    }

    /**
     * @param string $pattern
     * @param array<string, string> $parameters Identifier => value
     * @return Path
     */
    public function format(string $pattern, array $parameters = []): Path {
        $path = '';

        /** @var array<Token> $tokens */
        $tokens = [...(new Tokenizer($pattern))->tokenize()];
        $count = count($tokens);

        for ($position = 0; $position < $count; $position++) {
            $literal = $tokens[$position]->getLiteral();

            switch ($tokens[$position]->getType()) {
                case TokenType::BRACKET_L: {
                    $identAndClosingBracketFollows = ($position + 2 < $count)
                        && $tokens[$position + 1]->getType() === TokenType::IDENT
                        && $tokens[$position + 2]->getType() === TokenType::BRACKET_R;

                    if (!$identAndClosingBracketFollows) {
                        throw new RouteCompilerException("Illegal token '$literal'");
                    }

                    $ident = $tokens[$position + 1]->getLiteral();
                    if (!isset($parameters[$ident])) {
                        throw new RouteCompilerException("Identifier '$ident' is not present in parameters");
                    }

                    $path .= $parameters[$ident];
                    $position += 2;
                    break;
                }

                case TokenType::IDENT: {
                    $path .= $literal;
                    break;
                }

                case TokenType::SLASH: {
                    $isPreviousSlash = isset($tokens[$position - 1])
                        && $tokens[$position - 1]->getType() === TokenType::SLASH;
                    if ($isPreviousSlash) {
                        if ($this->config->isMergeConsecutiveSlashes()) {
                            break;
                        }

                        throw new RouteCompilerException("Illegal token '$literal'. Cannot parse consecutive slashes");
                    }

                    $path .= '/';
                    break;
                }

                case TokenType::ANY: {
                    if (!isset($parameters['*'])) {
                        throw new RouteCompilerException("Any identifier '*' is not present in parameters");
                    }

                    $path .= $parameters['*'];
                    break;
                }

                case TokenType::ANY_TERMINATOR: {
                    if (!isset($parameters['**'])) {
                        throw new RouteCompilerException("Any terminator identifier '**' is not present in parameters");
                    }

                    $path .= $parameters['**'];
                    break;
                }

                case TokenType::BRACKET_R: throw new RouteCompilerException("Unexpected token '$literal'");
                case TokenType::ILLEGAL: throw new RouteCompilerException("Illegal token '$literal'");

                case TokenType::EOF: {
                    break 2;
                }
            }
        }

        return Path::from($path);
    }

    /**
     * @param string $pattern
     * @return bool
     */
    public function isDynamic(string $pattern): bool {
        $tokenizer = new Tokenizer($pattern);

        foreach ($tokenizer->tokenize() as $token) {
            if ($token->getType() === TokenType::BRACKET_L) {
                return true;
            }
        }

        return false;
    }
}