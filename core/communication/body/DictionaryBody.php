<?php

namespace core\communication\body;

use core\collections\Dictionary;
use core\collections\dictionary\StrictMap;
use core\collections\StrictDictionary;
use core\communication\format\Format;
use core\communication\Request;
use core\communication\UploadedFile;
use core\storage\Temporary;
use core\io\FileReader;
use core\utils\Arrays;
use core\utils\Ini;
use core\utils\Php;
use core\utils\Strings;
use RuntimeException;

/**
 * @template-implements Dictionary
 */
class DictionaryBody implements RequestBody {
    use RequestBodyCache;

    public const FLAG_IS_ARRAY = 1;
    public const KEY_ITEMS = "items";

    private static bool $isTextSupported = true;

    public static function setIsTextSupported(bool $isTextSupported): void {
        self::$isTextSupported = $isTextSupported;
    }



    protected StrictDictionary $fields;
    protected StrictDictionary $files;



    public function supports(string $format): bool {
        return match ($format) {
            Format::IDENT_JSON,
            Format::IDENT_FORM_URLENCODED => true,

            Format::IDENT_TEXT => self::$isTextSupported,

            default => false,
        };
    }

    public function parse(Request $request): static {
        if ($instance = $this->bodyCache_get($request)) {
            return $instance;
        }

        switch ($format = $request->getFormat()) {
            case Format::IDENT_FORM_URLENCODED: {
                $reader = $request->getBodyReader();

                if (!(empty($_POST) && empty($_FILES))) {
                    $this->parseSuperGlobals();
                    break;
                }

                if ($request->isMultipart()) {
                    $this->parseMultipart($reader);
                    break;
                }

                $this->fields = new StrictMap();
                $this->fields->load(Strings::parseUrlEncoded($reader->readAll()));

                $this->files = new StrictMap();
                break;
            }

            case Format::IDENT_TEXT: {
                if (!self::$isTextSupported) {
                    throw new RuntimeException("Format: '$format' is not supported");
                }

                // Cascade to Format::IDENT_JSON
            }

            case Format::IDENT_JSON: {
                $this->files = new StrictMap();
                $this->fields = new StrictMap();

                $json = json_decode($request->getBodyReader()->readAll());

                if ($json == null) {
                    break;
                }

                if (is_array($json)) {
                    $this->fields
                        ->getMap()
                        ->setFlag(self::FLAG_IS_ARRAY);

                    $this->fields->set(self::KEY_ITEMS, $json);
                }

                foreach ($json as $key => $value) {
                    $this->fields->set($key, $value);
                }

                break;
            }

            default: {
                throw new RuntimeException("Format: '$format' is not supported");
            }
        }

        return $this->bodyCache_set($request, $this);
    }

    public function parseMultipart(FileReader $content): void {
        $chunkSize = 8192;

        $buffer = '';
        while (!$content->isEndOfFile()) {
            $buffer .= $char = $content->readCharacter();
            if ($char === "\n") {
                break;
            }
        }

        $pos = strpos($buffer, "\n");
        if ($pos === false) {
            $this->parseSuperGlobals();
            return;
        }

        $boundaryLine = substr($buffer, 0, $pos + 1);
        $buffer = substr($buffer, $pos + 1);

        $isUnixStyle = !str_ends_with($boundaryLine, "\r\n");
        $boundary = rtrim($boundaryLine);

        $newLine = $isUnixStyle
            ? "\n"
            : "\r\n";
        $boundaryMarker = $newLine . $boundary;
        $sectionMarker = str_repeat($newLine, 2);

        $fields = [];
        $files = [];

        $maxSize = Strings::toBytes(Php::get(Ini::UPLOAD_MAX_FILESIZE));

        while (!$content->isEndOfFile() || $buffer !== '') {
            while (!str_contains($buffer, $sectionMarker)) {
                if ($content->isEndOfFile()) {
                    break;
                }

                $buffer .= $content->read($chunkSize);
            }

            $headerEnd = strpos($buffer, $sectionMarker);
            if ($headerEnd === false) {
                break;
            }

            $headerString = substr($buffer, 0, $headerEnd);
            $buffer = substr($buffer, $headerEnd + strlen($sectionMarker));

            $headers = [];
            foreach (explode($newLine, $headerString) as $header) {
                if (!str_contains($header, ': ')) {
                    continue;
                }

                [$headerName, $headerValue] = explode(': ', $header, 2);
                $headers[$headerName] = FormDataHeader::from($headerValue);
            }

            if (!isset($headers['Content-Disposition'])) {
                continue;
            }

            $disposition = $headers['Content-Disposition'];
            if (!$disposition->has('name')) {
                continue;
            }

            $name = $disposition->get('name');
            $isFile = $disposition->has('filename');

            $fileName = $isFile
                ? $disposition->get('filename')
                : null;
            $type = isset($headers['Content-Type'])
                ? $headers['Content-Type']->getLiteral()
                : 'application/octet-stream';

            $size = 0;
            $temporaryPath = null;
            $temporary = null;

            if ($isFile) {
                $temporaryPath = Temporary::file('upload_');
                $temporary = fopen($temporaryPath, 'wb');

                if ($temporary === false) {
                    Arrays::append($files, $name, new UploadedFile(
                        $fileName, $type, $size, UPLOAD_ERR_CANT_WRITE
                    ));
                    continue;
                }
            }

            // [Claude review] Rewritten loop. Problems with the previous version:
            //  1. Denial of service: when the body ended without the closing boundary (truncated/malformed request),
            //     the `$safeLength <= 0 -> continue` branch spun forever at EOF, pinning a PHP worker until
            //     max_execution_time.
            //  2. Performance/memory: data was only flushed after EOF, so the whole file was accumulated in
            //     $buffer and strpos() rescanned the ever-growing buffer after every 8 KiB read (O(n^2)).
            //     File data is now flushed to the temp file as soon as it is safely before any possible boundary.
            //  3. When flushed at EOF, text fields were split into several chunks and Arrays::append() turned
            //     them into arrays. Text fields are no longer flushed in chunks.
            //  4. Size limit: the final chunk was written regardless of $maxSize.
            //  5. After the boundary, only strlen($boundary) bytes were skipped although $boundaryMarker (newline +
            //     boundary) was matched; that left stray characters in front of the next part's headers.
            $isPartial = false;
            $markerLength = strlen($boundaryMarker);

            while (true) {
                $pos = strpos($buffer, $boundaryMarker);

                if ($pos !== false) {
                    $data = substr($buffer, 0, $pos);

                    if ($isFile) {
                        $size += strlen($data);

                        if ($size <= $maxSize) {
                            fwrite($temporary, $data);
                        }
                    } else {
                        Arrays::append($fields, $name, $data);
                    }

                    $buffer = substr($buffer, $pos + $markerLength);
                    break;
                }

                if ($content->isEndOfFile()) {
                    // Body ended without the closing boundary -> discard this part
                    $isPartial = true;
                    $buffer = '';
                    break;
                }

                // Keep the last (marker length - 1) bytes, they may be the beginning of a boundary split by a read
                if ($isFile && ($safeLength = strlen($buffer) - $markerLength + 1) > 0) {
                    $data = substr($buffer, 0, $safeLength);
                    $buffer = substr($buffer, $safeLength);
                    $size += strlen($data);

                    if ($size <= $maxSize) {
                        fwrite($temporary, $data);
                    }
                }

                $buffer .= $content->read($chunkSize);
            }

            if (!$isFile) {
                continue;
            }

            fflush($temporary);
            fclose($temporary);

            if ($isPartial) {
                @unlink($temporaryPath);
                Arrays::append($files, $name, new UploadedFile(
                    $fileName, $type, $size,
                    UPLOAD_ERR_PARTIAL
                ));
                continue;
            }

            if ($size > $maxSize) {
                @unlink($temporaryPath);
                Arrays::append($files, $name, new UploadedFile(
                    $fileName, $type, $size,
                    UPLOAD_ERR_INI_SIZE
                ));
                continue;
            }

            Arrays::append($files, $name, new UploadedFile(
                $fileName, $type, $size,
                UPLOAD_ERR_OK,
                $temporaryPath
            ));
        }

        $this->fields = new StrictMap($fields);
        $this->files = new StrictMap($files);
    }

    public function parseSuperGlobals(): void {
        $this->files = new StrictMap();
        $this->fields = new StrictMap($_POST);

        foreach ($_FILES as $name => $value) {
            if (is_array($value['name'])) {
                $array = [];

                $count = count($value['name']);
                for ($i = 0; $i < $count; $i++) {
                    $array[] = new UploadedFile(
                        $value['name'][$i],
                        $value['type'][$i],
                        $value['size'][$i],
                        $value['error'][$i],
                        $value['tmp_name'][$i]
                    );
                }

                $this->files->set($name, $array);
                continue;
            }

            $this->files->set($name, new UploadedFile(
                $value['name'],
                $value['type'],
                $value['size'],
                $value['error'],
                $value['tmp_name']
            ));
        }
    }

    public function getFields(): StrictDictionary {
        if (!isset($this->fields)) {
            throw new RuntimeException("Error: Using fields before parsing request");
        }

        return $this->fields;
    }

    /**
     * @return StrictDictionary<UploadedFile>
     */
    public function getFiles(): StrictDictionary {
        if (!isset($this->files)) {
            throw new RuntimeException("Error: Using files before parsing request");
        }

        return $this->files;
    }
}