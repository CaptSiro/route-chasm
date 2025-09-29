<?php

namespace core\communication\parser;

use core\Active;
use core\App;
use core\collections\dictionary\StrictMap;
use core\communication\Format;
use core\communication\Request;
use core\communication\UploadedFile;
use core\http\HttpCode;
use core\Singleton;
use core\utils\Arrays;
use core\utils\Strings;

class FormBodyParser implements RequestBodyParser {
    use Active;
    use Singleton;

    public static function parseMultipart(string $content): RequestBody {
        $position = strpos($content, "\r");
        if ($position === false) {
            App::getInstance()
                ->getResponse()
                ->sendMessage(
                    "Invalid request (boundary not found)",
                    HttpCode::CE_BAD_REQUEST
                );
        }

        $boundary = substr($content, 0, $position);
        $values = [];
        $files = [];

        foreach (explode($boundary, $content) as $section) {
            $section = trim($section);
            if ($section === '' || $section === '--') {
                continue;
            }

            if (!str_contains($section, "\r\n\r\n")) {
                continue;
            }

            [$headerString, $value] = explode("\r\n\r\n", $section, 2);
            /**
             * @var array<FormDataHeader> $headers
             */
            $headers = [];

            foreach (explode("\r\n", $headerString) as $header) {
                [$name, $h] = explode(': ', $header, 2);
                $headers[$name] = FormDataHeader::from($h);
            }

            if (!isset($headers['Content-Disposition'])) {
                continue;
            }

            $disposition = $headers['Content-Disposition'];
            if (!$disposition->has('name')) {
                continue;
            }

            $name = $disposition->get('name');
            if (!$disposition->has('filename')) {
                Arrays::append($values, $name, $value);
                continue;
            }

            $fileName = $disposition->get('filename');
            $type = isset($headers['Content-Type']) ? $headers['Content-Type']->getLiteral() : 'application/octet-stream';
            $size = mb_strlen($value, '8bit');

            if ($size > Strings::toBytes(ini_get('upload_max_filesize'))) {
                Arrays::append($files, $name, new UploadedFile(
                    $fileName, $type, $size,
                    UPLOAD_ERR_INI_SIZE
                ));
                continue;
            }

            $temporary = tmpfile();
            if ($temporary === false) {
                Arrays::append($files, $name, new UploadedFile(
                    $fileName, $type, $size,
                    UPLOAD_ERR_CANT_WRITE
                ));
                continue;
            }

            $metadata = stream_get_meta_data($temporary);
            if (empty($metadata['uri'])) {
                @fclose($temporary);
                Arrays::append($files, $name, new UploadedFile(
                    $fileName, $type, $size,
                    UPLOAD_ERR_CANT_WRITE
                ));
                continue;
            }

            fwrite($temporary, $value);
            @fclose($temporary);
            Arrays::append($files, $name, new UploadedFile(
                $fileName, $type, $size,
                UPLOAD_ERR_OK,
                $metadata['uri']
            ));
        }

        return new RequestBody(new StrictMap($values), new StrictMap($files));
    }

    public static function parseSuperGlobals(): RequestBody {
        $files = new StrictMap();

        foreach ($_FILES as $name => $value) {
            if (is_array($value)) {
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
        $raw = $request->getBodyRaw();

        if ($raw === '') {
            if (!empty($_POST) || !empty($_FILES)) {
                return static::parseSuperGlobals();
            }

            return new RequestBody(
                new StrictMap(),
                new StrictMap()
            );
        }

        if ($request->isMultipart()) {
            return static::parseMultipart($raw);
        }

        $body->load(Strings::parseUrlEncoded($raw));
        return new RequestBody($body, new StrictMap());
    }

    public function supports(string $format): bool {
        return $this->isActive
            && $format === Format::IDENT_FORM_URLENCODED;
    }
}