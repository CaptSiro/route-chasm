<?php

namespace patterns;

use core\Singleton;

class Ident implements Pattern {
    use Singleton;



    protected Charset $first;
    protected Charset $rest;

    public function __construct() {
        $this->first = (new Charset())
            ->addRange('A', 'Z')
            ->addRange('a', 'z');

        $this->rest = (new Charset())
            ->addRange('0', '9')
            ->addRange('A', 'Z')
            ->addRange('a', 'z')
            ->add('-');
    }



    function match(?string $value): bool {
        return $this->first->match($value[0])
            && $this->rest->match(substr($value, 1));
    }

    function matchPipeline(Pipeline $pipeline, ?string &$match): bool {
        $first = $this->first->matchSequence($pipeline, 1, $match);
        if (!$first) {
            return false;
        }

        return $this->rest->matchPipeline($pipeline, $match);
    }
}