<?php

namespace core\translation;

use core\url\UrlPath;
use core\utils\Strings;

class UrlPathTranslator implements Translator {
    protected Translator $segments;

    public function __construct() {
        $this->segments = new StringTranslator(
            fn($x) => Strings::urlPathSegment($x)
        );
    }



    public function getSegments(): Translator {
        return $this->segments;
    }

    public function add(mixed $source): array {
        $target = [];

        foreach (UrlPath::segmented($source) as $segment) {
            $target[] = $this->segments->add($segment);
        }

        return $target;
    }

    public function getTarget(mixed $source): ?array {
        $target = [];

        foreach (UrlPath::segmented($source) as $segment) {
            $t = $this->segments->getTarget($segment);
            if (is_null($t)) {
                return null;
            }

            $target[] = $t;
        }

        return $target;
    }

    public function getSource(mixed $target): ?array {
        $source = [];

        foreach (UrlPath::segmented($target) as $segment) {
            $s = $this->segments->getSource($segment);
            if (is_null($s)) {
                return null;
            }

            $source[] = $s;
        }

        return $source;
    }
}