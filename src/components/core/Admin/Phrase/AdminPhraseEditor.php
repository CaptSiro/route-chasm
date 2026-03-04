<?php

namespace components\core\Admin\Phrase;

use components\core\Admin\Nexus\Editor\AdminNexusEditor;
use core\App;
use core\communication\Request;
use core\communication\Response;
use core\http\Http;
use core\http\HttpCode;
use core\route\RouteNode;
use core\RouteChasmEnvironment;
use core\url\Url;
use models\core\Language\Lexicon\Phrase;
use models\core\Language\Lexicon\PhraseEditorBehavior;
use models\core\Language\Lexicon\Translation;
use modules\ai\OpenAi;

class AdminPhraseEditor extends AdminNexusEditor {
    public const LEXICON_GROUP = 'admin.phrase.editor';
    public const QUERY_ID = 'id';



    protected PhraseEditorBehavior $phraseBehavior;

    public function __construct(PhraseEditorBehavior $behaviour = new PhraseEditorBehavior()) {
        parent::__construct($this->phraseBehavior = $behaviour);
        $this->setLexiconGroup(self::LEXICON_GROUP);
        $this->setTemplate(AdminNexusEditor::getTemplateResourceStatic());
        $this->setHeaderContent(new AdminPhraseAiTranslator($this));
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
            $id = $request->getUrl()->getQuery()->get(self::QUERY_ID);
            /** @var Phrase|null $phrase */
            $phrase = $this->context->createModel($id);

            if (is_null($phrase)) {
                $response->sendMessage(
                    $this->tr('Phrase not found'),
                    HttpCode::CE_NOT_FOUND
                );
            }

            $client = OpenAi::fromEnv();
            $result = $client->chat(
                AdminPhraseAiTranslator::createRequest($phrase)
            );

            $translations = AdminPhraseAiTranslator::parseTranslations($phrase, $result);
            if (empty($translations)) {
                $response->sendMessage(
                    $this->tr('Unable to translate'),
                    HttpCode::SE_SERVICE_UNAVAILABLE
                );
            }

            $phrase->deleteTranslations();
            $this->phraseBehavior->submitTranslations($phrase, $translations);

            $response->setStatus(HttpCode::S_OK);
            $response->flush();
        }));
    }

    public function getTranslationLink(int $languageId): Url {
        $url = $this->context->getEditorLink();

        $url
            ->setQueryArgument(RouteChasmEnvironment::QUERY_LANGUAGE_ID, $languageId)
            ->getPath()->append('translation');

        return $url;
    }

    public function getAiTranslateLink(): Url {
        $url = $this->context->getEditorLink();

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