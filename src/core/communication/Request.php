<?php

namespace core\communication;

use core\App;
use core\collections\dictionary\Session;
use core\collections\dictionary\StrictMap;
use core\collections\dictionary\StrictStack;
use core\collections\StrictDictionary;
use core\http\HttpHeader;
use core\io\FileReader;
use core\locale\LanguageSelector;
use core\locale\selectors\DefaultSelector;
use core\route\Path;
use core\url\Url;
use models\core\Domain\Domain;
use models\core\Language\Language;

class Request {
    public const PATH_INDEX = '__path_index';

    public const PARAM_ANY = "*";
    public const PARAM_ANY_TERMINATOR = "**";

    
    
    public static function test(?App $app = null, ?Url $url = null, ?string $httpMethod = "GET"): self {
        $format = new RequestFormat();
        $format->setFormatMatcher(new FormatMatcher());

        $ret = new self(
            $app ?? new App(),
            $format,
            $url ?? Url::fromRequest(),
            new StrictMap(),
        );

        $ret->httpMethod = $httpMethod;
        return $ret;
    }



    protected string $httpMethod;

    protected ?array $headers;

    protected Session $session;

    /**
     * @var StrictStack<string>
     */
    protected StrictStack $param;

    readonly protected StrictMap $data;
    readonly protected StrictDictionary $body;
    readonly protected Domain $domain;

    /**
     * @var StrictDictionary<UploadedFile>
     */
    readonly protected StrictDictionary $files;



    protected LanguageSelector $languageSelector;
    protected ?Language $language;
    private bool $isBodyParsed = false;



    public function __construct(
        readonly protected App $app,
        readonly protected LimitedFormat $format,
        readonly protected Url $url,
        readonly protected StrictDictionary $cookies,
    ) {
        $this->languageSelector = new DefaultSelector();
        $this->httpMethod = $_SERVER["REQUEST_METHOD"];
        $this->headers = null;
        $this->param = new StrictStack();
        $this->data = new StrictMap();

        $this->session = new Session(
            $this->domain = Domain::fromUrl($this->url)
        );
    }



    public function getFormat(): string {
        return $this->format->getIdentifier($this);
    }

    public function getUrl(): Url {
        return $this->url;
    }

    public function getBodyReader(): FileReader {
        return new FileReader('php://input');
    }

    public function getBody(): StrictDictionary {
        if (!$this->isBodyParsed) {
            $parsed = App::getInstance()->parseBody($this);
            $this->body = $parsed->body;
            $this->files = $parsed->files;
            $this->isBodyParsed = true;
        }

        return $this->body;
    }

    /**
     * @return StrictDictionary<UploadedFile>
     */
    public function getFiles(): StrictDictionary {
        if (!$this->isBodyParsed) {
            $parsed = App::getInstance()->parseBody($this);
            $this->body = $parsed->body;
            $this->files = $parsed->files;
            $this->isBodyParsed = true;
        }

        return $this->files;
    }

    public function getRemainingPath(): Path {
        $index = $this->data->get(self::PATH_INDEX, 0);
        return Path::from($this->url->getPath()->toString(), $index);
    }

    public function getDomain(): Domain {
        return $this->domain;
    }

    public function setLanguageSelector(LanguageSelector $languageSelector): void {
        $this->languageSelector = $languageSelector;
    }

    public function getLanguage(): Language {
        if (isset($this->language)) {
            return $this->language;
        }

        $selected = $this->languageSelector->select($this);
        if (!is_null($selected)) {
            return $this->language = Language::fromCode($selected)
                ?? App::getDefaultLanguage();
        }

        return $this->language = App::getDefaultLanguage();
    }

    public function getHeaders(): ?array {
        if ($this->headers === null) {
            $this->headers = apache_request_headers();
        }

        return $this->headers;
    }

    public function getHttpMethod(): string {
        return $this->httpMethod;
    }

    public function getCookies(): StrictDictionary {
        return $this->cookies;
    }

    /**
     * @return StrictStack<string>
     */
    public function getParam(): StrictStack {
        return $this->param;
    }

    public function getAnyParam(): ?string {
        return $this->param->get(Request::PARAM_ANY_TERMINATOR);
    }

    public function getSession(): ?Session {
        return $this->session;
    }

    public function getHeader(string $name): ?string {
        if ($this->headers === null) {
            $this->headers = apache_request_headers();
        }

        return $this->headers[$name] ?? null;
    }

    public function setHeader(string $name, string $value): void {
        if ($this->headers === null) {
            $this->headers = apache_request_headers();
        }

        $this->headers[$name] = $value;
    }

    public function get(string $variable, mixed $or = null) {
        return $this->data->get($variable, $or);
    }

    public function getFatal(string $variable) {
        return $this->data->getStrict($variable);
    }

    public function set(string $name, mixed $value): void {
        $this->data->set($name, $value);
    }

    public function exists(string $variable): bool {
        return $this->data->exists($variable);
    }

    public function isMultipart(): bool {
        $header = $this->getHeader(HttpHeader::CONTENT_TYPE);
        if (is_null($header)) {
            return false;
        }

        return str_starts_with($header, 'multipart/form-data');
    }

    public function __debugInfo(): ?array {
        if (!$this->isBodyParsed) {
            return [
                'httpMethod' => $this->httpMethod,
                'headers' => $this->headers,
                'url' => $this->url->toString(),
                'body' => '*not-parsed*',
                'files' => '*not-parsed*',
                'cookies' => $this->cookies,
                'domain' => $this->domain
            ];
        }

        return [
            'httpMethod' => $this->httpMethod,
            'headers' => $this->headers,
            'url' => $this->url->toString(),
            'body' => $this->body,
            'files' => $this->files,
            'cookies' => $this->cookies,
            'domain' => $this->domain
        ];
    }
}