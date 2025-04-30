<?php

namespace core\forms\controls;

use core\communication\Request;
use core\utils\Strings;

class CsrfField extends HiddenField {
    public const FIELD_NAME = 'csrf';
    public const FIELD_VALID_NAME = 'csrf_valid';

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
        if (!is_null($isValid = $request->get(self::FIELD_VALID_NAME))) {
            return $isValid;
        }

        $isValid = $request->getBody()->remove(self::FIELD_NAME) === self::getCsrf($request);
        $request->set(self::FIELD_VALID_NAME, $isValid);
        return $isValid;
    }



    public function __construct(Request $request) {
        parent::__construct(self::FIELD_NAME, self::getCsrf($request));
    }
}