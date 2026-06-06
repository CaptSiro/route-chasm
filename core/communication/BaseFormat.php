<?php

namespace core\communication;

trait BaseFormat {
    private FormatMatcher $matcher;

    public function setFormatMatcher(FormatMatcher $matcher): self {
        $this->matcher = $matcher;
        return $this;
    }
}