<?php

namespace components\pages\AiGeneratedPage;

use components\ai\AiRequest;
use components\ai\InputMessage;
use components\ai\PageGeneration\PageGeneration;
use components\ai\Schema\ObjectSchema;
use components\ai\Schema\Schema;
use components\ai\Schema\StringSchema;
use components\core\Admin\Nexus\Editor;
use components\core\Admin\Nexus\Editor\EditorBehavior;
use components\core\Admin\Nexus\Editor\EditorBehaviorAction;
use components\layout\Layout;
use core\App;
use core\database\sql\Model;
use core\forms\Form;
use core\ResourceLoader;
use core\sideloader\importers\Javascript\Javascript;
use core\view\View;
use models\core\Page\AiPage;
use modules\ai\OpenAi;

class AiPageEditorBehavior implements EditorBehavior {
    use ResourceLoader;



    public function __construct(
        protected EditorBehavior $behavior,
        protected AiPageTemplate $context
    ) {}



    public function setEditor(Editor $editor): void {
        $this->behavior->setEditor($editor);
    }

    public function initForm(Form $form, ?Model $model): ?View {
        Javascript::import($this->getResource('ai-page-generator.js'));
        $form->setOnSubmitSuccess('aiPageGenerator_success');
        $form->setOnSubmitFailure('aiPageGenerator_failure');
        return $this->behavior->initForm($form, $model);
    }

    public function addControls(Layout $layout, ?Model $model): ?View {
        return $this->behavior->addControls($layout, $model);
    }

    public function onSubmit(Model $model, EditorBehaviorAction $action): ?View {
        $body = App::getInstance()
            ->getRequest()
            ->getBody();

        if ($action === EditorBehaviorAction::UPDATE && $model instanceof AiPage) {
            $client = OpenAi::fromEnv();

            $request = new AiRequest('gpt-4o-mini');

            $schema = new Schema(
                'webpage_generation',
                (new ObjectSchema())
                    ->add('html', new StringSchema())
                    ->add('css', new StringSchema())
                    ->add('js', new StringSchema())
                    ->setRequired(['html', 'css', 'js'])
            );

            $description = $body->get('description');

            $request
                ->setSchema($schema)
                ->add(new PageGeneration(InputMessage::ROLE_SYSTEM, $description))
                ->add(new PageGeneration(InputMessage::ROLE_USER, $description));

            if (!is_null($response = $client->parseResponse($client->chat($request)))) {
                $page = $model->getPage();
                $page
                    ->get(AiPageTemplate::DATA_ITEM_HTML)
                    ->write($response['html']);

                $page
                    ->get(AiPageTemplate::DATA_ITEM_JS)
                    ->write($response['js']);

                $page
                    ->get(AiPageTemplate::DATA_ITEM_CSS)
                    ->write($response['css']);
            }
        }

        return $this->behavior->onSubmit($model, $action);
    }
}