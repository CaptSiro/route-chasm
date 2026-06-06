<?php

namespace core\communication\parser;

use core\collections\StrictDictionary;
use core\communication\UploadedFile;

readonly class RequestBody {
    /**
     * @param StrictDictionary<mixed> $body
     * @param StrictDictionary<UploadedFile> $files
     */
    public function __construct(
        public StrictDictionary $body,
        public StrictDictionary $files
    ) {}
}