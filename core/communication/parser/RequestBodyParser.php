<?php

namespace core\communication\parser;

use core\communication\Request;

interface RequestBodyParser {
    public function parse(Request $request): RequestBody;
    public function supports(string $format): bool;
}