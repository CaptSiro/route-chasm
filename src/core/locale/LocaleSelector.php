<?php

namespace core\locale;

use core\communication\Request;

interface LocaleSelector {
    public function select(Request $request): ?string;
}