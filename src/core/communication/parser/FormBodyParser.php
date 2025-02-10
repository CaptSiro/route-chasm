<?php

namespace core\communication\parser;

use core\Active;
use core\App;
use core\collection\StrictMap;
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

    public static function parseMultipart(Request $request): RequestBody {
        $content = $request->getBodyRaw();
        $position = strpos($content, "\r");
        if ($position === false) {
            App::getInstance()
                ->getResponse()
                ->error(
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
             * @var FormDataHeader[] $headers
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



    public function parse(Request $request): RequestBody {
        $body = new StrictMap();

        if ($request->isMultipart()) {
            return self::parseMultipart($request);
        }

        $body->load(Strings::parseUrlEncoded($request->getBodyRaw()));
        return new RequestBody($body, new StrictMap());
    }

    public function supports(string $format): bool {
        return $this->isActive
            && $format === Format::IDENT_FORM_URLENCODED;
    }
}