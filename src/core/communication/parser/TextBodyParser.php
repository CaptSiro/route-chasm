<?php

namespace core\communication\parser;

use core\Active;
use core\collections\dictionary\StrictMap;
use core\communication\Format;
use core\communication\Request;
use core\Singleton;

class TextBodyParser implements RequestBodyParser {
    use Active;
    use Singleton;

    public const FLAG_IS_TEXT = 2;
    public const KEY_TEXT = "text";



    public function parse(Request $request): RequestBody {
        $body = new StrictMap();

        $body->getMap()->setFlag(self::FLAG_IS_TEXT);
        $body->set(self::KEY_TEXT, $request->getBodyReader()->readAll());

        return new RequestBody($body, new StrictMap());
    }

    public function supports(string $format): bool {
        return $this->isActive
            && $format === Format::IDENT_TEXT;
    }
}