<?php

namespace core\locale;

use core\App;
use models\Language\Language;
use models\Language\Lexicon\Phrase;

class Lexicon {
    public static function group(string $group): LexiconTranslator {
        return new LexiconTranslator($group);
    }

    public static function format(string $pattern, string $value): string {
        return str_replace('{}', $value, $pattern);
    }

    public static function translate(string $group, string $default, ?Language $defaultLanguage = null, ?Language $targetLanguage = null): string {
        $defaultLanguage ??= App::getDefaultLanguage();
        $targetLanguage ??= App::getInstance()
            ->getRequest()
            ->getLanguage();

        $phrase = Phrase::fromPair($group, $default, $defaultLanguage, create: true);
        return $phrase->translate($targetLanguage) ?? $default;
    }

    /**
     * @param string $group
     * @param string $default
     * @param Language|null $defaultLanguage
     * @param array<string, string> $templates
     * @return LexiconTemplate
     */
    public static function createTemplate(
        string $group,
        string $default,
        ?Language $defaultLanguage = null,
        array $templates = []
    ): LexiconTemplate {
        $defaultLanguage ??= App::getDefaultLanguage();
        $targetLanguage ??= App::getInstance()
            ->getRequest()
            ->getLanguage();

        $phrase = Phrase::fromPair($group, $default, $defaultLanguage);

        if (is_null($phrase)) {
            $phrase = Phrase::createTemplate($group, $default, $defaultLanguage, $templates);
        }

        return $phrase;
    }

    /**
     * @param string $group
     * @param string $default
     * @param string $value
     * @param Language|null $defaultLanguage
     * @param Language|null $targetLanguage
     * @param array<string, string> $templates
     * @return string
     */
    public static function translateTemplate(
        string $group,
        string $default,
        string $value,
        ?Language $defaultLanguage = null,
        ?Language $targetLanguage = null,
        array $templates = []
    ): string {
        return Lexicon::createTemplate(
            $group, $default, $defaultLanguage, $templates
        )->format($value, $targetLanguage);
    }
}