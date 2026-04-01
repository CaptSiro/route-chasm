<?php

namespace core\communication\parser;

use core\Active;
use core\App;
use core\collections\dictionary\StrictMap;
use core\communication\Format;
use core\communication\Request;
use core\communication\UploadedFile;
use core\data\Data;
use core\http\HttpCode;
use core\io\FileReader;
use core\Singleton;
use core\utils\Arrays;
use core\utils\Ini;
use core\utils\Php;
use core\utils\Strings;

class FormBodyParser implements RequestBodyParser {
    use Active;
    use Singleton;



    public static function parseMultipart(FileReader $content): RequestBody {
        App::getInstance()
            ->getResponse()
            ->setHeader('X-BodyParser-Function', __FUNCTION__);

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
            return self::parseSuperGlobals();
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

        $values = [];
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
                        Arrays::append($values, $name, $data);
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
                    Arrays::append($values, $name, $data);
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

        return new RequestBody(
            new StrictMap($values),
            new StrictMap($files)
        );
    }

    public static function parseSuperGlobals(): RequestBody {
        $files = new StrictMap();

        App::getInstance()
            ->getResponse()
            ->setHeader('X-BodyParser-Function', __FUNCTION__);

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

                $files->set($name, $array);
                continue;
            }

            $files->set($name, new UploadedFile(
                $value['name'],
                $value['type'],
                $value['size'],
                $value['error'],
                $value['tmp_name']
            ));
        }

        return new RequestBody(
            new StrictMap($_POST),
            $files
        );
    }



    public function parse(Request $request): RequestBody {
        $body = new StrictMap();
        $reader = $request->getBodyReader();

        if (!(empty($_POST) && empty($_FILES))) {
            return static::parseSuperGlobals();
        }

        if ($request->isMultipart()) {
            return static::parseMultipart($reader);
        }

        $body->load(Strings::parseUrlEncoded($reader->readAll()));
        return new RequestBody($body, new StrictMap());
    }

    public function supports(string $format): bool {
        return $this->isActive
            && $format === Format::IDENT_FORM_URLENCODED;
    }
}