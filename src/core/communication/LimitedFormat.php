<?php

namespace core\communication;

use core\Request;

interface LimitedFormat {
    public function getIdentifier(Request $request): string;
}