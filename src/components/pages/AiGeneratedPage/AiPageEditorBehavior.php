<?php

namespace components\pages\AiGeneratedPage;

use components\ai\InputMessage;
use components\ai\PageGeneration\PageGeneration;
use components\ai\Schema\ObjectSchema;
use components\ai\Schema\Schema;
use components\ai\Schema\StringSchema;
use components\core\Admin\Nexus\Editor;
use components\core\Admin\Nexus\Editor\EditorBehavior;
use components\core\Admin\Nexus\Editor\EditorBehaviorAction;
use components\layout\Accordion\Accordion;
use components\layout\Column\Column;
use components\layout\Layout;
use core\ai\clients\OpenAi;
use core\App;
use core\database\sql\Model;
use core\forms\Form;
use core\locale\LexiconUnit;
use core\ResourceLoader;
use core\sideloader\importers\Javascript\Javascript;
use core\view\View;
use models\core\Page\AiPage;
use models\core\Page\Page;
use RuntimeException;

class AiPageEditorBehavior implements EditorBehavior {
    use ResourceLoader, LexiconUnit;

    public const LEXICON_GROUP = 'editor.ai-generated-page';

    public const NAME_PROMPT = 'prompt';
    public const PROPERTY_WEBPAGE_HTML = 'html';
    public const PROPERTY_WEBPAGE_CSS = 'css';
    public const PROPERTY_WEBPAGE_JS = 'js';



    public function __construct(
        protected EditorBehavior $behavior,
        protected AiPageTemplate $context
    ) {
        $this->setLexiconGroup(self::LEXICON_GROUP);
    }



    public function setEditor(Editor $editor): void {
        $this->behavior->setEditor($editor);
    }

    public function initForm(Form $form, ?Model $model): ?View {
        Javascript::import($this->getResource('ai-page-generator.js'));
        return $this->behavior->initForm($form, $model);
    }

    public function addControls(Layout $layout, ?Model $model): ?View {
        $aiPage = $model instanceof Page
            ? AiPage::fromPage($model)
            : null;

        $column = new Column();
        $ret = $this->behavior->addControls($column, $aiPage);

        $layout->add(new Accordion($this->tr('AI Page Generation'), $column));
        return $ret;
    }

    public function onSubmit(Model $model, EditorBehaviorAction $action): ?View {
        $body = App::getInstance()
            ->getRequest()
            ->getBody();

        if (!($model instanceof Page)) {
            throw new RuntimeException($this->tr("Provided model must be type of Page"));
        }

        $aiPage = AiPage::fromPage($model, true);
        $samePrompt = !is_null($aiPage)
            && $aiPage->prompt === $body->get(self::NAME_PROMPT);

        if ($action === EditorBehaviorAction::UPDATE && !$samePrompt) {
            $client = OpenAi::fromEnv();
            $request = $client->createRequest();

            $schema = new Schema(
                'webpage_generation',
                (new ObjectSchema())
                    ->add(self::PROPERTY_WEBPAGE_HTML, new StringSchema())
                    ->add(self::PROPERTY_WEBPAGE_CSS, new StringSchema())
                    ->add(self::PROPERTY_WEBPAGE_JS, new StringSchema())
                    ->setRequired([self::PROPERTY_WEBPAGE_HTML, self::PROPERTY_WEBPAGE_CSS, self::PROPERTY_WEBPAGE_JS])
            );

            $description = $body->get(self::NAME_PROMPT);

            $request
                ->setSchema($schema)
                ->add(new PageGeneration(InputMessage::ROLE_SYSTEM, $description))
                ->add(new PageGeneration(InputMessage::ROLE_USER, $description));

            if (!is_null($response = $client->parseResponse($client->chat($request)))) {
                $model
                    ->get(AiPageTemplate::DATA_ITEM_HTML)
                    ->write($response[self::PROPERTY_WEBPAGE_HTML]);

                $model
                    ->get(AiPageTemplate::DATA_ITEM_JS)
                    ->write($response[self::PROPERTY_WEBPAGE_JS]);

                $model
                    ->get(AiPageTemplate::DATA_ITEM_CSS)
                    ->write($response[self::PROPERTY_WEBPAGE_CSS]);
            }
        }

        return $this->behavior->onSubmit($aiPage, $action);
    }
}