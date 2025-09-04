<?php

namespace core\locale\selectors;

use core\communication\Request;
use core\locale\LanguageSelector;

class QuerySelector implements LanguageSelector {
    public const QUERY_PARAMETER = 'l';
    public const QUERY_PARAMETER_LONG = 'language';



    public function select(Request $request): ?string {
        return $request->getUrl()->getQuery()->get(self::QUERY_PARAMETER)
            ?? $request->getUrl()->getQuery()->get(self::QUERY_PARAMETER_LONG);
    }
}