<?php

namespace core\locale;

use core\App;
use models\core\Language\Language;
use models\core\Language\Lexicon\Phrase;

class Lexicon {
    public static function format(string $pattern, string $value): string {
        return str_replace('{}', $value, $pattern);
    }

    public static function translate(string $group, string $default, ?Language $targetLanguage = null): string {
        $language = $targetLanguage ?? App::getInstance()->getRequest()->getLanguage();
        if ($language->code === App::getDefaultLanguage()->code) {
            return $default;
        }

        $phrase = Phrase::fromPair($group, $default, doCreate: true);
        return $phrase->translate($language);
    }

    /**
     * @param string $group
     * @param string $default
     * @param string $value
     * @param Language|null $targetLanguage
     * @param array<string, array<string>> $templates
     * @return string
     */
    public static function translateTemplate(string $group, string $default, string $value, ?Language $targetLanguage = null, array $templates = []): string {
        if (empty($rules)) {
            return $value;
        }

        $language = $targetLanguage ?? App::getInstance()->getRequest()->getLanguage();
        $phrase = Phrase::fromPair($group, $group);
        if (is_null($phrase)) {
            $phrase = Phrase::createTemplate($group, $default, $language, $templates);
        }

        return $phrase->translateTemplate($value, $language)
            ?? $phrase->formatDefault($value);
    }
}