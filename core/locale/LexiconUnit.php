<?php

namespace core\locale;

use locales\EnglishUS;
use models\Language\Language;

trait LexiconUnit {
    protected string $lexiconGroup;



    public function setLexiconGroup(string $lexiconGroup): void {
        $this->lexiconGroup = $lexiconGroup;
    }

    protected function getDefaultLanguage(): Language {
        return Language::fromLocale(EnglishUS::getInstance());
    }

    public function f(string $pattern, string $value): string {
        return str_replace('{}', $value, $pattern);
    }

    public function trg(string $group, string $default, ?Language $defaultLanguage = null, ?Language $targetLanguage = null): string {
        return Lexicon::translate(
            $group,
            $default,
            $defaultLanguage ?? $this->getDefaultLanguage(),
            $targetLanguage
        );
    }

    public function tr(string $default, ?Language $defaultLanguage = null, ?Language $targetLanguage = null): string {
        return $this->trg($this->lexiconGroup, $default, $defaultLanguage, $targetLanguage);
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
    public function trtg(
        string $group,
        string $default,
        string $value,
        ?Language $defaultLanguage = null,
        ?Language $targetLanguage = null,
        array $templates = []
    ): string {
        return Lexicon::translateTemplate(
            $group,
            $default,
            $value,
            $defaultLanguage ?? $this->getDefaultLanguage(),
            $targetLanguage,
            $templates
        );
    }

    /**
     * @param string $default
     * @param string $value
     * @param Language|null $defaultLanguage
     * @param Language|null $targetLanguage
     * @param array<string, string> $templates
     * @return string
     */
    public function trt(
        string $default,
        string $value,
        ?Language $defaultLanguage = null,
        ?Language $targetLanguage = null,
        array $templates = []
    ): string {
        return $this->trtg($this->lexiconGroup, $default, $value,
            $defaultLanguage, $targetLanguage, $templates);
    }

    /**
     * @param string $group
     * @param string $default
     * @param Language|null $defaultLanguage
     * @param array<string, string> $templates
     * @return LexiconTemplate
     */
    public function crtg(
        string $group,
        string $default,
        ?Language $defaultLanguage = null,
        array $templates = []
    ): LexiconTemplate {
        return Lexicon::createTemplate(
            $group,
            $default,
            $defaultLanguage,
            $templates
        );
    }

    /**
     * @param string $default
     * @param Language|null $defaultLanguage
     * @param array<string, string> $templates
     * @return LexiconTemplate
     */
    public function crt(
        string $default,
        ?Language $defaultLanguage = null,
        array $templates = []
    ): LexiconTemplate {
        return $this->crtg($this->lexiconGroup, $default, $defaultLanguage, $templates);
    }
}