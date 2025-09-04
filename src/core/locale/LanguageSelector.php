<?php

namespace core\locale;

use core\communication\Request;

interface LanguageSelector {
    public function select(Request $request): ?string;
}