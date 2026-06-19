<?php

namespace core\route\compiler;

class RouteCompilerConfig {
    public const REGEX_ANY = '.*';
    public const REGEX_ALPHA = '[a-zA-Z]';
    public const REGEX_ALPHANUMERIC = '[a-zA-Z0-9]';
    public const REGEX_IDENT = self::REGEX_ALPHA . self::REGEX_ALPHANUMERIC . '*';



    protected string $identRegex = self::REGEX_IDENT;
    protected string $anyRegex = self::REGEX_ANY;
    protected bool $mergeConsecutiveSlashes = true;



    public function getAnyRegex(): string {
        return $this->anyRegex;
    }

    /**
     * Set regex expression for '/**' or '/*' (ANY) route segment.
     *
     * @param string $anyRegex
     * @return self
     *
     * @see self::REGEX_ANY Is set as default
     */
    public function setAnyRegex(string $anyRegex): self {
        $this->anyRegex = $anyRegex;
        return $this;
    }

    public function getIdentRegex(): string {
        return $this->identRegex;
    }

    /**
     * Set regex expression for identifiers inside route template. By default, first character must be alpha and
     * following characters must be alphanumeric. Route '/[my-ident]' is not valid, while '/[myIdent]' is. You may alter
     * the identifier regex, but the passing identifiers must be valid for named capture groups
     *
     * @param string $identRegex
     * @return self
     *
     * @see self::REGEX_IDENT
     */
    public function setIdentRegex(string $identRegex): self {
        $this->identRegex = $identRegex;
        return $this;
    }

    public function isMergeConsecutiveSlashes(): bool {
        return $this->mergeConsecutiveSlashes;
    }

    /**
     * If merge consecutive slashes is set to false, routes containing consecutive slashes will resolve in compiler
     * exception. If the option is set to true, all consecutive slashes are merged. For example: route 'foo///bar'
     * -> route 'foo/bar'
     *
     * @param bool $mergeConsecutiveSlashes
     * @return self
     *
     * @see RouteCompilerException
     */
    public function setMergeConsecutiveSlashes(bool $mergeConsecutiveSlashes): self {
        $this->mergeConsecutiveSlashes = $mergeConsecutiveSlashes;
        return $this;
    }
}