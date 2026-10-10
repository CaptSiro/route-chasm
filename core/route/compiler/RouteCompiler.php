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
        // [Claude review] Compiled routes are cached across requests, see RouteCompiler::cache*()
        $key = $pattern . "\0" . json_encode($parameters) . "\0" . $this->config->getAnyRegex() . "\0"
            . $this->config->getIdentRegex() . "\0" . (int) $this->config->isMergeConsecutiveSlashes();

        if (!is_null($segments = self::cacheGet($key))) {
            $route = new Route();

            foreach ($segments as [$source, $regex, $isTerminal]) {
                $segment = new RouteSegment($source, $regex);
                if ($isTerminal) {
                    $segment->setFlag(RouteSegment::FLAG_IS_TERMINAL);
                }

                $route->add($segment);
            }

            return $route;
        }

        $route = $this->compile($pattern, $parameters);

        self::cacheSet($key, array_map(
            fn(RouteSegment $x) => [$x->getSource(), (string) $x, $x->hasFlag(RouteSegment::FLAG_IS_TERMINAL)],
            $route->getSegments()
        ));

        return $route;
    }

    /**
     * [Claude review] Route cache: `<storage>/route-cache.php` returns an array, so OPcache keeps it in shared memory
     * and loading it is ~free. Written at shutdown, only when a new pattern was compiled. Delete the file to reset.
     */
    public const CACHE_FILE = 'route-cache.php';
    public const CACHE_MAX_ENTRIES = 4096;

    private static ?array $cache = null;
    private static bool $isCacheDirty = false;

    protected static function getCacheFile(): ?string {
        $storage = function_exists('project_mounted')
            ? project_mounted('<storage>')
            : null;

        return is_null($storage)
            ? null
            : $storage . DIRECTORY_SEPARATOR . self::CACHE_FILE;
    }

    protected static function cacheGet(string $key): ?array {
        if (is_null(self::$cache)) {
            $file = self::getCacheFile();
            $loaded = !is_null($file) && is_file($file)
                ? include $file
                : [];

            self::$cache = is_array($loaded) ? $loaded : [];
        }

        return self::$cache[$key] ?? null;
    }

    protected static function cacheSet(string $key, array $segments): void {
        if (count(self::$cache) >= self::CACHE_MAX_ENTRIES) {
            // guards against unbounded growth if routes are ever built from dynamic input
            return;
        }

        self::$cache[$key] = $segments;

        if (!self::$isCacheDirty) {
            self::$isCacheDirty = true;
            register_shutdown_function([self::class, 'saveCache']);
        }
    }

    public static function saveCache(): void {
        if (!self::$isCacheDirty || is_null($file = self::getCacheFile())) {
            return;
        }

        // Write + rename so concurrent requests never include a half-written file
        $temporary = $file . '.' . getmypid() . '.tmp';
        if (file_put_contents($temporary, '<?php return ' . var_export(self::$cache, true) . ';' . PHP_EOL) !== false) {
            rename($temporary, $file);
        }

        self::$isCacheDirty = false;
    }

    protected function compile(string $pattern, array $parameters): Route {
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