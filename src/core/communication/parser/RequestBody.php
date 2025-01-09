<?php

namespace core\communication\parser;

use core\collection\StrictDictionary;

readonly class RequestBody {
    public function __construct(
        public StrictDictionary $body,
        public StrictDictionary $files
    ) {}
}