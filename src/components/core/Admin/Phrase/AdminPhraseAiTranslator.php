<?php

namespace components\core\Admin\Phrase;

use core\App;
use core\ResourceLoader;
use core\utils\Strings;
use core\view\Renderer;
use core\view\View;
use models\core\Language\Language;
use models\core\Language\Lexicon\Phrase;
use models\core\Language\Lexicon\Rule;
use models\core\Language\Lexicon\Translation;

class AdminPhraseAiTranslator implements View {
    use Renderer, ResourceLoader;

    public static function createRequest(Phrase $phrase): array {
        return $phrase->isDynamic
            ? self::createDynamicRequest($phrase)
            : self::createStaticRequest($phrase);
    }

    public static function createStaticRequest(Phrase $phrase): array {
        $languages = json_encode(Language::getCodes(), JSON_UNESCAPED_UNICODE);

        $userContent = <<<TXT
phrase: {$phrase->default}
group: {$phrase->getLexiconGroup()->name}
languages: {$languages}
TXT;

        return [
            "model" => "gpt-4o-mini",
            "text" => ["format" => ["type" => "json_object"]],
            "input" => [
                [
                    "role" => "system",
                    "content" => "Translate the given phrase into all target languages. Do not add explanations, notes, or context. Just return a JSON object mapping language_code -> translated_string."
                ],
                [
                    "role" => "user",
                    "content" => $userContent
                ]
            ]
        ];
    }

    public static function createDynamicRequest(Phrase $phrase): array {
        $languages = json_encode(Language::getCodes(), JSON_UNESCAPED_UNICODE);
        $rules = json_encode(Rule::getLabels(), JSON_UNESCAPED_UNICODE);

        $userContent = <<<TXT
phrase: {$phrase->default}
group: {$phrase->getLexiconGroup()->name}
languages: {$languages}
rules: {$rules}

Requirements:
- Translate the phrase into each target language.
- The phrase will contain "{}" which is a placeholder for a dynamic value.
- NEVER remove or replace "{}".
- "{}" must appear in every translation exactly once.
- Each language uses **ONLY THE RULES THAT APPLY TO IT**. Do NOT force all rules. If a language needs fewer rules, output fewer. If a language requires more, output more.
- Output a JSON object:
  {
    language: {
      rule: "translated string with {}"
    }
  }
- No comments, no explanations, no prose.
TXT;

        return [
            "model" => "gpt-4o-mini",
            "text" => ["format" => ["type" => "json_object"]],
            "input" => [
                [
                    "role" => "system",
                    "content" =>
                        "You are a translation engine with pluralization support. " .
                        "Your only job is to produce valid JSON according to the user's instructions. " .
                        "Do not add explanations. Do not change the '{}' placeholder."
                ],
                [
                    "role" => "user",
                    "content" => $userContent
                ]
            ]
        ];
    }

    public static function parseTranslations(Phrase $phrase, bool|string $result): array {
        if ($result === false) {
            return [];
        }

        $json = json_decode($result, associative: true);
        if (!isset($json['output'][0]['content'][0]['text'])) {
            return [];
        }

        $content = json_decode($json['output'][0]['content'][0]['text'], associative: true);

        return $phrase->isDynamic
            ? self::parseDynamicTranslations($content)
            : self::parseStaticTranslations($content);
    }

    private static function parseDynamicTranslations(array $result): array {
        $ret = [];
        $notFound = [];

        foreach ($result as $code => $rules) {
            if (is_null($language = Language::fromCode($code))) {
                $notFound[] = $code;
                continue;
            }

            foreach ($rules as $label => $translation) {
                if (is_null($rule = Rule::fromLabel((string) $label))) {
                    $notFound[] = $label;
                    continue;
                }

                $ret[] = [
                    Translation::NAME_RULE_ID => $rule->getId(),
                    Translation::NAME_LANGUAGE_ID => $language->getId(),
                    Translation::NAME_TRANSLATION => $translation,
                ];
            }
        }

        if (empty($ret)) {
            App::getInstance()
                ->getResponse()
                ->send(Strings::fromBuffer(fn() => var_dump($notFound)));
        }

        return $ret;
    }

    private static function parseStaticTranslations(array $result): array {
        $ret = [];

        foreach ($result as $code => $translation) {
            if (is_null($language = Language::fromCode($code))) {
                continue;
            }

            $ret[] = [
                Translation::NAME_LANGUAGE_ID => $language->getId(),
                Translation::NAME_TRANSLATION => $translation,
            ];
        }

        return $ret;
    }



    public function __construct(
        protected AdminPhraseEditor $context
    ) {}
}