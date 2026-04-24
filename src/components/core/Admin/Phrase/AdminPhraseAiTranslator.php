<?php

namespace components\core\Admin\Phrase;

use components\ai\DynamicTranslation\DynamicTranslation;
use components\ai\InputMessage;
use components\ai\StaticTranslation\StaticTranslation;
use core\ai\Client;
use core\ai\clients\OpenAi;
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

    public const AI_MODEL = 'gpt-4o-mini';



    public static function createRequest(Client $client, Phrase $phrase): View {
        return $phrase->isDynamic
            ? self::createDynamicRequest($client, $phrase)
            : self::createStaticRequest($client, $phrase);
    }

    public static function createStaticRequest(Client $client, Phrase $phrase): View {
        $request = $client->createRequest();

        OpenAi::addGenericJsonFormat($request);

        $request->add(new StaticTranslation(InputMessage::ROLE_SYSTEM, $phrase));
        $request->add(new StaticTranslation(InputMessage::ROLE_USER, $phrase));

        return $request;
    }

    public static function createDynamicRequest(Client $client, Phrase $phrase): View {
        $request = $client->createRequest();

        OpenAi::addGenericJsonFormat($request);

        $request->add(new DynamicTranslation(InputMessage::ROLE_SYSTEM, $phrase));
        $request->add(new DynamicTranslation(InputMessage::ROLE_USER, $phrase));

        return $request;
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
                    Translation::NAME_RULE_ID => $rule->id,
                    Translation::NAME_LANGUAGE_ID => $language->id,
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
                Translation::NAME_LANGUAGE_ID => $language->id,
                Translation::NAME_TRANSLATION => $translation,
            ];
        }

        return $ret;
    }



    public function __construct(
        protected AdminPhraseEditor $context
    ) {}
}