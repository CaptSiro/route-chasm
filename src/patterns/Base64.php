<?php

namespace patterns;

use core\Pipeline;
use core\Singleton;

class Base64 implements Pattern {
    use Singleton;



    protected Charset $charset;

    public function __construct() {
        $this->charset = (new Charset())
            ->addRange('0', '9')
            ->addRange('A', 'Z')
            ->addRange('a', 'z')
            ->add('_')
            ->add('-');
    }



    function match(?string $value): bool {
        return $this->charset->match($value);
    }

    function matchPipeline(Pipeline $pipeline, ?string &$match): bool {
        return $this->charset->matchPipeline($pipeline, $match);
    }
}