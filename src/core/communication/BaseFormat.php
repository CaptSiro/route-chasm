<?php

namespace core\communication;

trait BaseFormat {
    private FormatMatcher $matcher;

    public function setFormatMatcher(FormatMatcher $matcher): void {
        $this->matcher = $matcher;
    }
}