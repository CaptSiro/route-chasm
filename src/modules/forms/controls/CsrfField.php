<?php

namespace modules\forms\controls;

use core\communication\Request;
use core\utils\Strings;

class CsrfField extends HiddenField {
    public const FIELD_NAME = 'csrf';

    public static function getCsrf(Request $request): string {
        $csrf = $request->getSession()->get('csrf');
        if (!is_null($csrf)) {
            return $csrf;
        }

        $csrf = Strings::randomBase64(16);
        $request->getSession()->set('csrf', $csrf);
        return $csrf;
    }

    public static function check(Request $request): bool {
        return $request->getBody()->get(self::FIELD_NAME) === self::getCsrf($request);
    }



    public function __construct(Request $request) {
        parent::__construct(self::FIELD_NAME, self::getCsrf($request));
    }
}