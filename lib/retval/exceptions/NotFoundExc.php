<?php

namespace retval\exceptions;

class NotFoundExc extends Exc {
    public function __construct($msg) {
        parent::__construct($msg);
    }
}