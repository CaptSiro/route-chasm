<?php

namespace core;

use components\core\HttpError\HttpError;
use components\core\WebPage\WebPageContent;

class Response {
    // Informational
    public const CODE_CONTINUE = 100;
    public const CODE_SWITCHING_PROTOCOLS = 101;

    // Successful
    public const CODE_OK = 200;
    public const CODE_CREATED = 201;
    public const CODE_ACCEPTED = 202;
    public const CODE_NON_AUTHORITATIVE_INFORMATION = 203;
    public const CODE_NO_CONTENT = 204;
    public const CODE_RESET_CONTENT = 205;
    public const CODE_PARTIAL_CONTENT = 206;

    // Redirection
    public const CODE_MULTIPLE_CHOICES = 300;
    public const CODE_MOVED_PERMANENTLY = 301;
    public const CODE_FOUND = 302;
    public const CODE_SEE_OTHER = 303;
    public const CODE_NOT_MODIFIED = 304;
    public const CODE_USE_PROXY = 305;

    // Client Error
    public const CODE_BAD_REQUEST = 400;
    public const CODE_UNAUTHORIZED = 401;
    public const CODE_PAYMENT_REQUIRED = 402;
    public const CODE_FORBIDDEN = 403;
    public const CODE_NOT_FOUND = 404;
    public const CODE_METHOD_NOT_ALLOWED = 405;
    public const CODE_NOT_ACCEPTABLE = 406;
    public const CODE_PROXY_AUTHENTICATION_REQUIRED = 407;
    public const CODE_REQUEST_TIMEOUT = 408;
    public const CODE_CONFLICT = 409;
    public const CODE_GONE = 410;
    public const CODE_LENGTH_REQUIRED = 411;
    public const CODE_PRECONDITION_FAILED = 412;
    public const CODE_PAYLOAD_TOO_LARGE = 413;
    public const CODE_URI_TOO_LONG = 414;
    public const CODE_UNSUPPORTED_MEDIA_TYPE = 415;
    public const CODE_IM_A_TEAPOT = 418;

    // Server Error
    public const CODE_INTERNAL_SERVER_ERROR = 500;
    public const CODE_NOT_IMPLEMENTED = 501;
    public const CODE_BAD_GATEWAY = 502;
    public const CODE_SERVICE_UNAVAILABLE = 503;
    public const CODE_GATEWAY_TIMEOUT = 504;
    public const CODE_HTTP_VERSION_NOT_SUPPORTED = 505;



    protected array $headers;
    protected bool $headersSent;



    public function __construct() {
        $this->headers = [];
        $this->headersSent = false;
    }



    public function hasHeader(string $header): bool {
        return isset($this->headers[$header]);
    }

    public function setHeader(string $header, string $value): void {
        $this->headers[$header] = $value;
    }

    /**
     * @param array ...$headers Single header is tuple of two strings, name and value. Example: <code>"Location: /"</code> would be
     * <code>["Location" => "/"]</code>
     * @return void
     */
    public function setHeaders(array $headers): void {
        foreach ($headers as $header => $value) {
            $this->headers[$header] = $value;
        }
    }

    public function removeHeader(string $header): void {
        unset($this->headers[$header]);
    }

    public function removeAllHeaders(): void {
        $this->headers = [];
    }

    public function setStatus(int $code): void {
        http_response_code($code);
    }

    public function generateHeaders(): void {
        if ($this->headersSent) {
            return;
        }

        $this->headersSent = true;

        foreach ($this->headers as $header => $value) {
            header("$header: $value");
        }
    }

    /**
     * Exits the execution without sending any data but headers will be sent.
     */
    public function flush(): void {
        $this->generateHeaders();
        exit;
    }

    /**
     * Exits the execution.
     *
     * Sends string data to user.
     */
    public function send($text): void {
        $this->generateHeaders();
        echo $text;
        exit;
    }

    /**
     * Exits the execution.
     *
     * Parses object into JSON text representation and sends it to the user.
     */
    public function json($data, $flags = 0, $depth = 512): void {
        $this->generateHeaders();
        echo json_encode($data, $flags, $depth);
        exit;
    }

    /**
     * Exits the execution.
     *
     * Reads file and sends it contents to the user.
     *
     * **This function does not download the file on user's end. It only sends file's contents.**
     */
    public function readFile(string $file): void {
        if (!file_exists($file)) {
            $this->render(new HttpError("RequestFile not found: $file", self::CODE_NOT_FOUND));
        }

        $this->generateHeaders();
        readfile($file);
        exit;
    }

    /**
     * Exits the execution.
     *
     * Checks for valid file path and sets headers to download it.
     */
    public function download(string $file): void {
        $this->setHeaders([
            Http::HEADER_CONTENT_DESCRIPTION => "RequestFile Transfer",
            Http::HEADER_CONTENT_TYPE => 'application/octet-stream',
            Http::HEADER_CONTENT_DISPOSITION => "attachment; filename=" . basename($file),
            Http::HEADER_PREGMA => "public",
            Http::HEADER_CONTENT_LENGTH => filesize($file)
        ]);

        $this->readFile($file);
    }

    public function render(Render $render, ?string $template = null, bool $doFlushResponse = true): void {
        $this->generateHeaders();

        if ($render instanceof WebPageContent) {
            $render->call(App::getInstance()->getRequest(), $this);
        } else {
            echo $render->render($template);
        }

        if (!$doFlushResponse) {
            return;
        }

        exit;
    }

    /**
     * Redirects request to new URL.
     *
     * Do prepend home directory is used to dynamically prepend directory structure that is between server directory and this project's directory.
     *
     * www/ **my-project** /index.php -> localhost/ **my-project** / **'/my-project'** will be prepended
     *
     * `/api/user` -> `/my-project/api/user`
     *
     *
     * @param string $url accepts same URLs as Location header.
     * @param bool $doPrependHome
     * @return void
     */
    public function redirect(string $url, bool $doPrependHome = true): void {
        $this->setHeader(Http::HEADER_LOCATION, ($doPrependHome ? App::getInstance()->getHome() : "") . $url);
        $this->flush();
    }
}