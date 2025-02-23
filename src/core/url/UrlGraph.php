<?php

namespace core\url;

use core\translation\StringTranslator;
use core\translation\Translator;
use core\utils\Strings;

class UrlGraph {
    public const KEY_LEAF = 0;

    public static function isLeaf(?array $node): bool {
        if (is_null($node)) {
            return false;
        }

        return isset($node[self::KEY_LEAF]);
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



    public function add(string $path): void {
        $node = &$this->root;

        foreach (UrlPath::segmented($path) as $segment) {
            $target = $this->segments->add($segment);

            if (!isset($node[$target])) {
                $node[$target] = [];
            }

            $node = &$node[$target];
        }

        $node[self::KEY_LEAF] = true;
    }

    public function getSource(string $path): string {
        $node = &$this->root;
        $return = [];

        foreach (UrlPath::segmented($path) as $target) {
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
}