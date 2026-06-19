<?php

namespace core\locale\selectors;

use core\communication\Request;
use core\locale\LanguageSelector;
use core\RouteChasmEnvironment;

class QueryLanguageSelector implements LanguageSelector {
    public function select(Request $request): ?string {
        return $request->getUrl()->getQuery()->get(RouteChasmEnvironment::QUERY_LANGUAGE)
            ?? $request->getUrl()->getQuery()->get(RouteChasmEnvironment::QUERY_LANGUAGE_LONG);
    }
}