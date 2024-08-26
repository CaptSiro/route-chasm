<?php

namespace retval;

class Trace {
    public $file, $line;

    public function __construct($f, $l) {
        $this->file = $f;
        $this->line = $l;
    }
}