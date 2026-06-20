<?php

namespace core\communication\body;

use core\communication\format\Format;
use core\communication\Request;
use RuntimeException;

class TextBody implements RequestBody {
    use RequestBodyCache;

    protected string $text;



    public function supports(string $format): bool {
        return $format === Format::IDENT_TEXT;
    }

    public function parse(Request $request): static {
        if ($instance = $this->bodyCache_get($request)) {
            return $instance;
        }

        $this->text = $request->getBodyReader()->readAll();
        return $this->bodyCache_set($request, $this);
    }

    public function getText(): string {
        if (!isset($this->text)) {
            throw new RuntimeException("Error: Using text before parsing request");
        }

        return $this->text;
    }
}