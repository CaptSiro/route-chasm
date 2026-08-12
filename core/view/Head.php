<?php

namespace core\view;

use components\Search\Search;
use core\App;
use core\fs\FileSystem;
use core\route\Path;
use core\RouteChasmEnvironment;
use core\view\renderers\XmlRenderer;
use JsonSerializable;
use models\Language\Language;
use RuntimeException;

class Head extends Component implements JsonSerializable {
    public const PAYLOAD_TITLE = 'head:title';
    public const PAYLOAD_LANGUAGE = 'head:language';
    public const PAYLOAD_LANGUAGE_CODE = 'head:language_code';

    public const PAYLOAD_HTML_FAVICON = 'head:favicon';
    public const PAYLOAD_HTML_META = 'head:html_meta';
    public const PAYLOAD_HTML_ELEMENTS = 'head:html_elements';

    public const PAYLOAD_XML_VERSION = 'head:xml_version';
    public const PAYLOAD_XML_ENCODING = 'head:xml_encoding';



    protected Language $language;

    public function __construct(
        protected Payload $payload,
        ?Renderer $renderer = null
    ) {
        parent::__construct($renderer);
        XmlRenderer::setXmlTemplate($this);

        if (!is_null($env = App::getInstance()->getEnv())) {
            $this->addPropertyHtmlMeta('author', $env->get(RouteChasmEnvironment::ENV_PROJECT_AUTHOR));
        }

        $payload->addAllProperties(Head::PAYLOAD_HTML_ELEMENTS, [
            FileSystem::createApi(),
            Search::createApi()
        ]);
    }



    protected function forceArray(mixed $variable, string $exceptionMessage): array {
        if (!is_array($variable)) {
            throw new RuntimeException($exceptionMessage);
        }

        return $variable;
    }

    public function getTitle(): ?string {
        return $this->payload->getProperty(self::PAYLOAD_TITLE);
    }

    public function getLanguage(): Language {
        if (isset($this->language)) {
            return $this->language;
        }

        if (($language = $this->payload->getProperty(self::PAYLOAD_LANGUAGE)) instanceof Language) {
            return $this->language = $language;
        }

        if (is_null($code = $this->payload->getProperty(self::PAYLOAD_LANGUAGE_CODE))) {
            return $this->language = App::getInstance()
                ->getRequest()
                ->getLanguage();
        }

        if (is_null($language = Language::fromCode($code))) {
            throw new RuntimeException("Language '$code' not found");
        }

        return $this->language = $language;
    }

    public function setLanguage(Language $language): static {
        $this->language = $language;
        return $this;
    }

    public function getHtmlFavicon(): string {
        if (is_null($icon = $this->payload->getProperty(self::PAYLOAD_HTML_FAVICON))) {
            $icon = "/public/images/favicon.png";
        }

        return App::getInstance()
            ->attach(Path::resolve($icon));
    }

    public function getHtmlMeta(): array {
        return $this->forceArray(
            $this->payload->getProperty(self::PAYLOAD_HTML_META) ?? [],
            'Meta property is not type of array'
        );
    }

    public function getHtmlElements(): array {
        return $this->forceArray(
            $this->payload->getProperty(self::PAYLOAD_HTML_ELEMENTS) ?? [],
            'Elements property is not type of array'
        );
    }

    public function getXmlVersion(): string {
        return $this->payload->getProperty(self::PAYLOAD_XML_VERSION) ?? '1.0';
    }

    public function getXmlEncoding(): string {
        return $this->payload->getProperty(self::PAYLOAD_XML_ENCODING) ?? 'UTF-8';
    }



    public function jsonSerialize(): array {
        return [
            'title' => $this->getTitle(),
            'meta' => $this->getHtmlMeta()
        ];
    }
}