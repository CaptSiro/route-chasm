<?php

namespace core\communication\body;

use core\communication\Request;

interface RequestBody {
    public function supports(string $format): bool;

    public function parse(Request $request): static;
}