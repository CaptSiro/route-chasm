<?php

namespace core\communication;

use components\core\HttpMessage\HttpMessage;
use core\App;
use core\http\HttpCode;
use core\http\HttpHeader;
use core\view\BufferTransform;
use core\view\View;

class Response {
    /**
     * @see Response
     */
    public const EVENT_HEADERS_GENERATION = self::class .':headers-generation';

    /**
     * @see BufferTransform
     * @see Response::render()
     */
    public const EVENT_OB_TRANSFORM = self::class .':ob-transform';

    public static function test(?LimitedFormat $format = null): static {
        return new static(
            $format ?? (new ResponseFormat())->setFormatMatcher(new FormatMatcher())
        );
    }



    protected array $headers;
    protected bool $headersSent;



    public function __construct(
        readonly protected LimitedFormat $format
    ) {
        $this->headers = [];
        $this->headersSent = false;
    }



    public function getFormat(?Request $request = null): string {
        return $this->format->getIdentifier($request ?? App::getInstance()->getRequest());
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
    public function send(?string $text, bool $doFlushResponse = true): void {
        $this->generateHeaders();

        if (!is_null($text)) {
            echo $text;
        }

        if (!$doFlushResponse) {
            return;
        }

        $this->exit();
    }

    /**
     * Exits the execution.
     *
     * Parses object into JSON text representation and sends it to the user.
     */
    public function json($data, $flags = 0, $depth = 512): void {
        $this->setHeader(HttpHeader::CONTENT_TYPE, 'application/json');

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
    public function readFile(string $file, bool $doFlush = true): void {
        if (!file_exists($file)) {
            $this->sendMessage(
                "RequestFile not found: $file",
                HttpCode::CE_NOT_FOUND
            );
        }

        $this->generateHeaders();
        readfile($file);

        if ($doFlush) {
            $this->exit();
        }
    }

    /**
     * Exits the execution.
     *
     * Checks for valid file path and sets headers to download it.
     */
    public function download(string $file, ?string $name = null): void {
        $this->setHeaders([
            HttpHeader::CONTENT_DESCRIPTION => "RequestFile Transfer",
            HttpHeader::CONTENT_TYPE => 'application/octet-stream',
            HttpHeader::CONTENT_DISPOSITION => "attachment; filename=" . ($name ?? basename($file)),
            HttpHeader::PREGMA => "public",
            HttpHeader::CONTENT_LENGTH => filesize($file)
        ]);

        $this->readFile($file);
    }

    /**
     * Render object is rendered with given template to back buffer. Buffer contents may be transformed after rendering
     * is completed with <code>EVENT_OB_TRANSFORM</code> event listeners. <code>EVENT_OB_TRANSFORM</code> is able to
     * transform only data rendered from Render object. All data printed to output buffers prior to executing
     * Render::render() are not accessible to transform
     *
     * @param View $view
     * @param bool $doFlushResponse
     * @return void
     * @see Response::EVENT_OB_TRANSFORM
     */
    public function render(View $view, bool $doFlushResponse = true): void {
        ob_start();
        echo $view->render();

        $this->generateHeaders();

        $buffer = new BufferTransform(ob_get_clean());
        App::getInstance()->dispatch(self::EVENT_OB_TRANSFORM, $buffer);
        echo $buffer->getContents();

        if (!$doFlushResponse) {
            return;
        }

        $this->exit();
    }

    public function renderRoot(View $view, bool $doFlushResponse = true): void {
        $this->render($view->getRoot(), $doFlushResponse);
    }

    public function sendMessage(string $message, int $httpCode): void {
        $this->render(
            new HttpMessage(
                $message,
                $httpCode,
                1
            ),
        );
    }

    public function sendStatus(int $httpCode): void {
        $this->setStatus($httpCode);
        $this->flush();
    }

    /**
     * Redirects request to new URL.
     *
     * @param string $url accepts same URLs as Location header.
     * @return void
     */
    public function redirect(string $url): void {
        $this->setHeader(HttpHeader::LOCATION, $url);
        $this->flush();
    }
}