<?php

namespace patterns;

use core\Singleton;

class Number implements Pattern {
    use Singleton;



    protected Charset $charset;

    public function __construct() {
        $this->charset = (new Charset())
            ->addRange('0', '9');
    }



    function match(?string $value): bool {
        return $this->charset->match($value);
    }

    public function matchPipeline(Pipeline $pipeline, ?string &$match): bool {
        return $this->charset->matchPipeline($pipeline, $match);
    }
}