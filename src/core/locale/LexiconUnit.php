<?php

namespace core\locale;

use models\core\Language\Language;

trait LexiconUnit {
    public function f(string $pattern, string $value): string {
        return str_replace('{}', $value, $pattern);
    }

    public function tr(string $group, string $default, ?Language $targetLanguage = null): string {
        return Lexicon::translate($group, $default, $targetLanguage);
    }

    /**
     * @param string $group
     * @param string $default
     * @param string $value
     * @param Language|null $targetLanguage
     * @param array<string, array<string>> $templates
     * @return string
     */
    public function trt(string $group, string $default, string $value, ?Language $targetLanguage = null, array $templates = []): string {
        return Lexicon::translateTemplate($group, $default, $value, $targetLanguage, $templates);
    }
}