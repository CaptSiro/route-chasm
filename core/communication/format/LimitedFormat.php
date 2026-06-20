<?php

namespace core\communication\format;

use core\communication\Request;

interface LimitedFormat {
    public function getIdentifier(Request $request): string;
}