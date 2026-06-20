<?php

namespace core\communication\body;

use core\collections\Dictionary;
use core\collections\dictionary\StrictMap;
use core\collections\StrictDictionary;
use core\communication\format\Format;
use core\communication\Request;
use core\communication\UploadedFile;
use core\data\Data;
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



    protected StrictDictionary $fields;
    protected StrictDictionary $files;



    public function supports(string $format): bool {
        return match ($format) {
            Format::IDENT_JSON,
            Format::IDENT_FORM_URLENCODED => true,
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
        $tempDirectory = Data::namespace('temp', create: true);

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
                $temporaryPath = tempnam($tempDirectory, 'upload_');
                $temporary = fopen($temporaryPath, 'wb');

                if ($temporary === false) {
                    Arrays::append($files, $name, new UploadedFile(
                        $fileName, $type, $size, UPLOAD_ERR_CANT_WRITE
                    ));
                    continue;
                }
            }

            while (true) {
                if (!str_contains($buffer, $boundaryMarker) && !$content->isEndOfFile()) {
                    $buffer .= $content->read($chunkSize);
                    continue;
                }

                $pos = strpos($buffer, $boundaryMarker);
                if ($pos === false) {
                    $safeLength = strlen($buffer) - strlen($boundaryMarker) - 4;

                    if ($safeLength <= 0) {
                        continue;
                    }

                    $data = substr($buffer, 0, $safeLength);
                    $buffer = substr($buffer, $safeLength);

                    if (!$isFile) {
                        var_dump($name);
                        Arrays::append($fields, $name, $data);
                        continue;
                    }

                    $size += strlen($data);

                    if ($size <= $maxSize) {
                        fwrite($temporary, $data);
                    }

                    continue;
                }

                $data = substr($buffer, 0, $pos);

                if ($isFile) {
                    $size += strlen($data);
                    fwrite($temporary, $data);
                } else {
                    Arrays::append($fields, $name, $data);
                }

                $buffer = substr($buffer, $pos + strlen($boundary));
                break;
            }

            if (!$isFile) {
                continue;
            }

            fflush($temporary);
            fclose($temporary);

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