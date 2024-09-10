<?php

namespace core;

use components\core\HttpError\HttpError;
use components\core\WebPage\WebPageContent;
use core\http\HttpCode;
use core\http\HttpHeader;

class Response {
    public const TYPE_TEXT = "TEXT";
    public const TYPE_JSON = "JSON";
    public const TYPE_HTML = "HTML";

    public const EVENT_HEADERS_GENERATION = self::class .':headers-generation';



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
        App::getInstance()->dispatch(self::EVENT_HEADERS_GENERATION, $this);

        foreach ($this->headers as $header => $value) {
            header("$header: $value");
        }
    }

    protected function exit(): void {
        App::getInstance()->dispatch(App::EVENT_SHUTDOWN, $this);
        exit;
    }

    /**
     * Exits the execution without sending any data but headers will be sent.
     */
    public function flush(): void {
        $this->generateHeaders();
        $this->exit();
    }

    /**
     * Exits the execution.
     *
     * Sends string data to user.
     */
    public function send($text): void {
        $this->generateHeaders();
        echo $text;
        $this->exit();
    }

    /**
     * Exits the execution.
     *
     * Parses object into JSON text representation and sends it to the user.
     */
    public function json($data, $flags = 0, $depth = 512): void {
        $this->generateHeaders();
        echo json_encode($data, $flags, $depth);
        $this->exit();
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
            $this->render(new HttpError("RequestFile not found: $file", HttpCode::CE_NOT_FOUND));
        }

        $this->generateHeaders();
        readfile($file);
        $this->exit();
    }

    /**
     * Exits the execution.
     *
     * Checks for valid file path and sets headers to download it.
     */
    public function download(string $file): void {
        $this->setHeaders([
            HttpHeader::CONTENT_DESCRIPTION => "RequestFile Transfer",
            HttpHeader::CONTENT_TYPE => 'application/octet-stream',
            HttpHeader::CONTENT_DISPOSITION => "attachment; filename=" . basename($file),
            HttpHeader::PREGMA => "public",
            HttpHeader::CONTENT_LENGTH => filesize($file)
        ]);

        $this->readFile($file);
    }

    public function render(Render $render, ?string $template = null, bool $doFlushResponse = true): void {
        $this->generateHeaders();

        if ($render instanceof WebPageContent) {
            $render->execute(App::getInstance()->getRequest(), $this);
        } else {
            echo $render->render($template);
        }

        if (!$doFlushResponse) {
            return;
        }

        $this->exit();
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
        $this->setHeader(HttpHeader::LOCATION, ($doPrependHome ? App::getInstance()->getHome() : "") . $url);
        $this->flush();
    }
}