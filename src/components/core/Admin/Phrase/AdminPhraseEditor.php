<?php

namespace components\core\Admin\Phrase;

use components\core\Admin\Nexus\Editor\AdminNexusEditor;
use components\core\Admin\Nexus\Editor\EditorBehavior;
use core\communication\Request;
use core\communication\Response;
use core\http\Http;
use core\route\RouteNode;
use core\url\Url;
use models\core\Language\Lexicon\Translation;

class AdminPhraseEditor extends AdminNexusEditor {
    public const QUERY_LANGUAGE_ID = 'language-id';

    public function __construct(EditorBehavior $behaviour) {
        parent::__construct($behaviour);
        $this->setTemplate(AdminNexusEditor::getTemplateResourceStatic());
    }



    public function onBind(RouteNode $bindingPoint): void {
        parent::onBind($bindingPoint);

        $router = $bindingPoint->getRouter();
        $router->use('/translation', Http::get(function (Request $request, Response $response) {
            $languageId = intval($request->getUrl()->getQuery()->getStrict(self::QUERY_LANGUAGE_ID));
            $control = Translation::createDynamicTranslationControl($languageId)->render();
            $button = $this->createAddTranslationButton($languageId);

            $response->send($control . $button);
        }));
    }

    public function getTranslationLink(int $languageId): Url {
        $url = $this->context->getEditorLink();

        $url
            ->setQueryArgument(self::QUERY_LANGUAGE_ID, $languageId)
            ->getPath()->append('translation');

        return $url;
    }

    public function createAddTranslationButton(int $languageId): string {
        $url = $this->getTranslationLink($languageId);
        return "<div><button x-get='$url' x-swap='outer' type='button'>Add Translation</button></div>";
    }
}