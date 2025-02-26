<?php

namespace core\url;

use core\translation\StringTranslator;
use core\translation\Translator;
use core\utils\Arrays;
use core\utils\Strings;

class UrlGraph {
    public const KEY_LEAF = 0;

    public static function isLeaf(?array $node): bool {
        if (is_null($node)) {
            return false;
        }

        return isset($node[self::KEY_LEAF]);
    }

    public static function getItem(?array $node): mixed {
        if (is_null($node)) {
            return null;
        }

        return $node[self::KEY_LEAF] ?? null;
    }



    protected array $root = [];

    public function __construct(
        protected ?Translator $segments = null
    ) {
        if (is_null($this->segments)) {
            $this->segments = new StringTranslator(
                fn($x) => Strings::urlPathSegment($x)
            );
        }
    }



    public function hasItem(): bool {
        return static::isLeaf($this->root);
    }

    public function add(string $path, mixed $item): void {
        $node = &$this->root;

        foreach (Arrays::explode('/', $path) as $segment) {
            $target = $this->segments->add($segment);

            if (!isset($node[$target])) {
                $node[$target] = [];
            }

            $node = &$node[$target];
        }

        $node[self::KEY_LEAF] = $item;
    }

    public function getPathSource(string $path): string {
        $node = &$this->root;
        $return = [];

        foreach (Arrays::explode('/', $path) as $target) {
            if (!isset($node[$target])) {
                break;
            }

            $return[] = $this->segments->getSource($target);
            $node = &$node[$target];
        }

        return implode('/', $return);
    }

    public function getRoot(): array {
        return $this->root;
    }

    public function getSubGraph(string $target): ?static {
        if (!isset($this->root[$target])) {
            return null;
        }

        $graph = new static();
        $graph->root = &$this->root[$target];

        return $graph;
    }

    public function getRootItem(): mixed {
        return self::getItem($this->root);
    }
}