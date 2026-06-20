<?php

namespace core\communication\format;

trait BaseFormat {
    private FormatMatcher $matcher;

    public function setFormatMatcher(FormatMatcher $matcher): self {
        $this->matcher = $matcher;
        return $this;
    }
}