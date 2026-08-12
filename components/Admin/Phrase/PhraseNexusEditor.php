<?php

namespace components\Admin\Phrase;

use components\nexus\NexusEditor;
use core\ai\clients\OpenAi;
use core\App;
use core\communication\Request;
use core\communication\Response;
use core\database\sql\ModelDescription;
use core\http\Http;
use core\http\HttpCode;
use core\route\RouteNode;
use core\RouteChasmEnvironment;
use core\url\Url;
use models\Language\Lexicon\Phrase;
use models\Language\Lexicon\PhraseEditorBehavior;
use models\Language\Lexicon\Translation;

class PhraseNexusEditor extends NexusEditor {
    public const LEXICON_GROUP = 'admin.phrase.editor';
    public const QUERY_ID = 'id';



    protected PhraseEditorBehavior $phraseBehavior;

    public function __construct() {
        parent::__construct(
            ModelDescription::extract(Phrase::class),
            $this->phraseBehavior = new PhraseEditorBehavior()
        );

        $this->setLexiconGroup(self::LEXICON_GROUP);
        $this->setTemplate(NexusEditor::getTemplateResourceStatic());
        $this->setTemplateSlot($this::SLOT_HEADER_CONTENT, new PhraseAiTranslator($this));
    }



    public function onBind(RouteNode $bindingPoint): void {
        parent::onBind($bindingPoint);

        $router = $bindingPoint->getRouter();
        $router->use('/translation', Http::get(function (Request $request, Response $response) {
            $languageId = intval($request->getUrl()->getQuery()->getStrict(RouteChasmEnvironment::QUERY_LANGUAGE_ID));
            $control = Translation::createDynamicTranslationControl($languageId)->render();
            $button = $this->createAddTranslationButton($languageId);

            $response->send($control . $button);
        }));

        $router->use('/ai-translate', Http::get(function (Request $request, Response $response) {
            $messagePhraseNotFound = $this->tr('Phrase not found');
            $messageUnableToTranslate = $this->tr('Unable to translate');

            $id = $request->getUrl()->getQuery()->get(self::QUERY_ID);
            /** @var Phrase|null $phrase */
            $phrase = $this->modelDescription
                ->getFactory()
                ->fromId($id);

            if (is_null($phrase)) {
                $response->sendMessage($messagePhraseNotFound, HttpCode::CE_NOT_FOUND);
            }

            $client = OpenAi::fromEnv();
            $result = $client->chat(
                PhraseAiTranslator::createRequest($client, $phrase)
            );

            $translations = PhraseAiTranslator::parseTranslations($phrase, $result);
            if (empty($translations)) {
                $response->sendMessage($messageUnableToTranslate, HttpCode::SE_SERVICE_UNAVAILABLE);
            }

            $phrase->deleteTranslations();
            $this->phraseBehavior->submitTranslations($phrase, $translations);

            $response->setStatus(HttpCode::S_OK);
            $response->flush();
        }));
    }

    public function getTranslationLink(int $languageId): Url {
        $url = $this->context->getCreateUrl();

        $url
            ->setQueryArgument(RouteChasmEnvironment::QUERY_LANGUAGE_ID, $languageId)
            ->getPath()->append('translation');

        return $url;
    }

    public function getAiTranslateLink(): Url {
        $url = $this->context->getCreateUrl();

        $url
            ->setQueryArgument(
                self::QUERY_ID,
                App::getInstance()->getRequest()->getParam()->get('id')
            )
            ->getPath()->append('ai-translate');

        return $url;
    }

    public function createAddTranslationButton(int $languageId): string {
        $url = $this->getTranslationLink($languageId);
        return "<div><button x-get='$url' x-swap='outer' type='button'>Add Translation</button></div>";
    }
}