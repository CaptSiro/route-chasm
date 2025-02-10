<?php

namespace retval;

class Trace {
    public string $file, $line;

    public function __construct($f, $l) {
        $this->file = $f;
        $this->line = $l;
    }
}