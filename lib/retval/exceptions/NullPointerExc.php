<?php

namespace retval\exceptions;

class NullPointerExc extends Exc {
    public function __construct($msg) {
        parent::__construct($msg);
    }
}