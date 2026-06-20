<?php

namespace core\communication\body;

use core\communication\format\Format;
use core\communication\Request;
use DOMDocument;
use DOMElement;
use DOMXPath;
use InvalidArgumentException;
use RuntimeException;

class XmlBody implements RequestBody {
    use RequestBodyCache;

    protected DOMDocument $document;



    public function supports(string $format): bool {
        return $format === Format::IDENT_XML;
    }

    public function parse(Request $request): static {
        if ($instance = $this->bodyCache_get($request)) {
            return $instance;
        }

        $xml = $request->getBodyReader()->readAll();

        $document = new DOMDocument();
        $document->preserveWhiteSpace = false;

        if (!$document->loadXML($xml)) {
            throw new InvalidArgumentException('Invalid XML body');
        }

        $this->document = $document;

        return $this->bodyCache_set($request, $this);
    }

    public function getDocument(): DOMDocument {
        if (!isset($this->document)) {
            throw new RuntimeException("Error: Using document before parsing request");
        }

        return $this->document;
    }

    public function getRoot(): ?DOMElement {
        return $this->getDocument()->documentElement;
    }

    public function xpath(): DOMXPath {
        return new DOMXPath($this->getDocument());
    }
}