<?php

namespace retval\exceptions;

class InvalidArgumentExc extends Exc {
    public function __construct($msg) {
        parent::__construct($msg);
    }
}