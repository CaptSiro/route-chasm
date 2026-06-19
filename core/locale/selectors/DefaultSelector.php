<?php

namespace core\locale\selectors;

use core\communication\Request;
use core\locale\LanguageSelector;

class DefaultSelector implements LanguageSelector {
    protected AcceptLanguageSelector $acceptLanguageSelector;
    protected QueryLanguageSelector $querySelector;



    public function __construct() {
        $this->acceptLanguageSelector = new AcceptLanguageSelector();
        $this->querySelector = new QueryLanguageSelector();
    }



    public function select(Request $request): ?string {
        return $this->querySelector->select($request)
            ?? $this->acceptLanguageSelector->select($request);
    }
}