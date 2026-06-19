<?php

namespace core\communication\parser;

use core\Active;
use core\collections\dictionary\StrictMap;
use core\communication\Format;
use core\communication\Request;
use core\Flags;
use core\Singleton;

class JsonBodyParser implements RequestBodyParser {
    use Active;
    use Singleton;
    use Flags;

    public const FLAG_IS_ARRAY = 1;
    public const FLAG_SUPPORTS_TEXT = 1 << 16;
    public const KEY_ARRAY = "array";



    public function __construct() {
        $this->setFlag(self::FLAG_SUPPORTS_TEXT);
    }

    public function parse(Request $request): RequestBody {
        $body = new StrictMap();
        $json = json_decode($request->getBodyReader()->readAll());

        if ($json == null) {
            return new RequestBody($body, new StrictMap());
        }

        if (is_array($json)) {
            $body->getMap()->setFlag(self::FLAG_IS_ARRAY);
            $body->set(self::KEY_ARRAY, $json);
            return new RequestBody($body, new StrictMap());
        }

        foreach ($json as $key => $value) {
            $body->set($key, $value);
        }

        return new RequestBody($body, new StrictMap());
    }

    public function supports(string $format): bool {
        return $this->isActive
            && $format === Format::IDENT_JSON
            || ($this->hasFlag(self::FLAG_SUPPORTS_TEXT) && $format === Format::IDENT_TEXT);
    }
}