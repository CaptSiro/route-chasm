<?php

namespace core\locale;

use models\core\Language\Language;

trait LexiconUnit {
    protected string $lexiconGroup;



    public function setLexiconGroup(string $lexiconGroup): void {
        $this->lexiconGroup = $lexiconGroup;
    }

    public function f(string $pattern, string $value): string {
        return str_replace('{}', $value, $pattern);
    }

    public function tr(string $default, ?Language $targetLanguage = null): string {
        return Lexicon::translate($this->lexiconGroup, $default, $targetLanguage);
    }

    public function trg(string $group, string $default, ?Language $targetLanguage = null): string {
        return Lexicon::translate($group, $default, $targetLanguage);
    }

    /**
     * @param string $default
     * @param string $value
     * @param Language|null $targetLanguage
     * @param array<string, string> $templates
     * @return string
     */
    public function trt(
        string $default,
        string $value,
        ?Language $targetLanguage = null,
        array $templates = []
    ): string {
        return Lexicon::translateTemplate(
            $this->lexiconGroup,
            $default,
            $value,
            $targetLanguage,
            $templates
        );
    }

    /**
     * @param string $group
     * @param string $default
     * @param string $value
     * @param Language|null $targetLanguage
     * @param array<string, string> $templates
     * @return string
     */
    public function trtg(
        string $group,
        string $default,
        string $value,
        ?Language $targetLanguage = null,
        array $templates = []
    ): string {
        return Lexicon::translateTemplate(
            $group,
            $default,
            $value,
            $targetLanguage,
            $templates
        );
    }
}