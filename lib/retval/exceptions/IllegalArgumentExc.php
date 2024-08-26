<?php

namespace retval\exceptions;

class IllegalArgumentExc extends Exc {
    public function __construct($msg) {
        parent::__construct($msg);
    }
}