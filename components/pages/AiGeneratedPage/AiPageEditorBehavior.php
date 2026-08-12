<?php

namespace components\pages\AiGeneratedPage;

use components\ai\InputMessage;
use components\ai\PageGeneration\PageGeneration;
use components\ai\Schema\ObjectSchema;
use components\ai\Schema\Schema;
use components\ai\Schema\StringSchema;
use components\forms\Form;
use components\layout\Accordion;
use components\layout\Column;
use components\nexus\NexusEditorAction;
use components\nexus\NexusEditor;
use components\nexus\NexusEditorBehavior;
use core\ai\clients\OpenAi;
use core\App;
use core\communication\body\DictionaryBody;
use core\database\sql\Model;
use core\locale\LexiconUnit;
use core\ResourceLoader;
use core\sideloader\importers\Javascript\Javascript;
use core\view\Container;
use core\view\View;
use models\Page\AiPage;
use models\Page\Page;
use RuntimeException;

class AiPageEditorBehavior extends NexusEditorBehavior {
    use ResourceLoader, LexiconUnit;

    public const LEXICON_GROUP = 'editor.ai-generated-page';

    public const NAME_PROMPT = 'prompt';
    public const PROPERTY_WEBPAGE_HTML = 'html';
    public const PROPERTY_WEBPAGE_CSS = 'css';
    public const PROPERTY_WEBPAGE_JS = 'js';



    public function __construct(
        protected NexusEditorBehavior $behavior,
        protected AiPageTemplate $context
    ) {
        $this->setLexiconGroup(self::LEXICON_GROUP);
    }



    public function getTitle(): string {
        return $this->tr('AI Page Properties');
    }

    public function onFormInitialization(NexusEditor $editor, Form $form, ?Model $model): ?View {
        Javascript::import($this->getResource($this->getClass() . '.js'));
        return $this->behavior->onFormInitialization($editor, $form, $model);
    }

    public function onFormGeneration(Container $container, ?Model $model): ?View {
        $aiPage = $model instanceof Page
            ? AiPage::fromPage($model)
            : null;

        $column = new Column();
        $ret = $this->behavior->onFormGeneration($column, $aiPage);

        $container->add(new Accordion($this->tr('AI Page Generation'), $column));
        return $ret;
    }

    public function onSubmit(Model $model, NexusEditorAction $action): ?View {
        $fields = App::getInstance()
            ->getRequest()
            ->body(DictionaryBody::class)
            ->getFields();

        if (!($model instanceof Page)) {
            throw new RuntimeException($this->tr("Provided model must be type of Page"));
        }

        $aiPage = AiPage::fromPage($model, true);
        $prompt = $fields->get(self::NAME_PROMPT);
        $samePrompt = !is_null($aiPage)
            && $aiPage->prompt === $prompt;

        if ($action === NexusEditorAction::UPDATE && !$samePrompt && !is_null($prompt)) {
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

            $request
                ->setSchema($schema)
                ->add(new PageGeneration(InputMessage::ROLE_SYSTEM, $prompt))
                ->add(new PageGeneration(InputMessage::ROLE_USER, $prompt));

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