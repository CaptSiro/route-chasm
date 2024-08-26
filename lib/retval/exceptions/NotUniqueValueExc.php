<?php

namespace retval\exceptions;

class NotUniqueValueExc extends Exc {
    public function __construct($msg) {
        parent::__construct($msg);
    }
}