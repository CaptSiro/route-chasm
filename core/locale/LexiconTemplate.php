<?php

namespace core\locale;

use core\communication\Request;
use models\Language\Language;

interface LexiconTemplate {
    /**
     * @param string $value
     * @param ?Language $targetLanguage By default translates to Language defined in request
     * @return string
     *
     * @see Request::getLanguage()
     */
    public function format(string $value, ?Language $targetLanguage = null): string;
}