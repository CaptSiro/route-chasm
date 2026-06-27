<?php

namespace core\view2;

use core\App;
use core\route\Path;
use core\view2\renderers\HtmlRenderer;
use core\view2\renderers\XmlRenderer;
use JsonSerializable;
use models\Language\Language;
use RuntimeException;

class Head extends Component implements JsonSerializable {
    public const PROPERTY_TITLE = 'head_title';
    public const PROPERTY_LANGUAGE_CODE = 'head_language_code';

    public const PROPERTY_HTML_FAVICON = 'head_favicon';
    public const PROPERTY_HTML_META = 'head_html_meta';
    public const PROPERTY_HTML_ELEMENTS = 'head_html_elements';

    public const PROPERTY_XML_VERSION = 'head_xml_version';
    public const PROPERTY_XML_ENCODING = 'head_xml_encoding';



    protected Language $language;

    public function __construct(
        protected Payload $payload,
        Renderer $renderer = new HtmlRenderer()
    ) {
        parent::__construct($renderer);

        XmlRenderer::setXmlTemplate($this);
    }



    protected function forceArray(mixed $variable, string $exceptionMessage): array {
        if (!is_array($variable)) {
            throw new RuntimeException($exceptionMessage);
        }

        return $variable;
    }

    public function getTitle(): ?string {
        return $this->payload->get(self::PROPERTY_TITLE);
    }

    public function getLanguage(): Language {
        if (isset($this->language)) {
            return $this->language;
        }

        if (is_null($code = $this->payload->get(self::PROPERTY_LANGUAGE_CODE))) {
            $this->language = App::getInstance()
                ->getRequest()
                ->getLanguage();
        }

        if (is_null($language = Language::fromCode($code))) {
            throw new RuntimeException("Language '$code' not found");
        }

        return $this->language = $language;
    }

    public function getHtmlFavicon(): string {
        if (is_null($icon = $this->payload->get(self::PROPERTY_HTML_FAVICON))) {
            $icon = "/public/images/favicon.png";
        }

        return App::getInstance()
            ->attach(Path::resolve($icon));
    }

    public function getHtmlMeta(): array {
        return $this->forceArray(
            $this->payload->get(self::PROPERTY_HTML_META) ?? [],
            'Meta property is not type of array'
        );
    }

    public function getHtmlElements(): array {
        return $this->forceArray(
            $this->payload->get(self::PROPERTY_HTML_ELEMENTS) ?? [],
            'Elements property is not type of array'
        );
    }

    public function getXmlVersion(): string {
        return $this->payload->get(self::PROPERTY_XML_VERSION) ?? '1.0';
    }

    public function getXmlEncoding(): string {
        return $this->payload->get(self::PROPERTY_XML_ENCODING) ?? 'UTF-8';
    }



    public function jsonSerialize(): array {
        return [
            'title' => $this->getTitle(),
            'meta' => $this->getHtmlMeta()
        ];
    }
}